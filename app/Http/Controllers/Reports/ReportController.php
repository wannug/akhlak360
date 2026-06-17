<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentResult;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\ReportExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const CATEGORIES = [
        'Perlu Pengembangan',
        'Cukup',
        'Baik',
        'Sangat Baik',
    ];

    private const HEADERS = [
        'period',
        'employee_number',
        'employee_name',
        'department',
        'position',
        'amanah_score',
        'kompeten_score',
        'harmonis_score',
        'loyal_score',
        'adaptif_score',
        'kolaboratif_score',
        'self_score',
        'others_score',
        'gap_score',
        'final_score',
        'category',
        'talent_mapping_category',
        'weakest_core_value',
        'idp_recommendation',
    ];

    public function index(Request $request): View
    {
        return view('reports.index', [
            'periods' => AssessmentPeriod::orderByDesc('year')->orderByDesc('start_date')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'categories' => self::CATEGORIES,
            'results' => $this->filteredQuery($request)
                ->paginate(15)
                ->withQueryString(),
            'excelAvailable' => class_exists('Maatwebsite\\Excel\\Facades\\Excel'),
            'pdfAvailable' => class_exists('Barryvdh\\DomPDF\\Facade\\Pdf'),
        ]);
    }

    public function history(): View
    {
        return view('reports.history', [
            'exports' => ReportExport::with(['user', 'assessmentPeriod'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function csv(Request $request): StreamedResponse
    {
        $filename = 'akhlak360-report-'.now()->format('Ymd-His').'.csv';
        $this->recordExport($request, 'csv', $filename, 'generated');

        return response()->streamDownload(function () use ($request): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, self::HEADERS);

            $this->filteredQuery($request)
                ->chunk(100, function (Collection $results) use ($handle): void {
                    foreach ($results as $result) {
                        fputcsv($handle, $this->row($result));
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function excel(Request $request): RedirectResponse
    {
        if (! class_exists('Maatwebsite\\Excel\\Facades\\Excel')) {
            $this->recordExport($request, 'excel', null, 'failed');

            return back()->with('warning', 'Excel export requires maatwebsite/excel to be installed.');
        }

        $this->recordExport($request, 'excel', null, 'failed');

        return back()->with('warning', 'Excel package detected, but this MVP export adapter is not configured.');
    }

    public function pdf(Request $request): RedirectResponse
    {
        if (! class_exists('Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $this->recordExport($request, 'pdf', null, 'failed');

            return back()->with('warning', 'PDF export requires barryvdh/laravel-dompdf to be installed.');
        }

        $this->recordExport($request, 'pdf', null, 'failed');

        return back()->with('warning', 'PDF package detected, but this MVP export adapter is not configured.');
    }

    private function filteredQuery(Request $request): Builder
    {
        return AssessmentResult::query()
            ->with(['assessmentPeriod', 'employee.department', 'employee.position'])
            ->with(['employee.idpRecommendations' => fn ($query) => $query
                ->when($request->filled('period_id'), fn ($query) => $query->where('assessment_period_id', $request->integer('period_id')))])
            ->when($request->filled('period_id'), fn (Builder $query) => $query->where('assessment_period_id', $request->integer('period_id')))
            ->when($request->filled('department_id'), fn (Builder $query) => $query->whereHas(
                'employee',
                fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $request->integer('department_id'))
            ))
            ->when($request->filled('category'), fn (Builder $query) => $query->where('category', $request->category))
            ->when($request->boolean('below_threshold'), fn (Builder $query) => $query->whereRaw(
                'assessment_results.final_score < (select threshold_score from assessment_periods where assessment_periods.id = assessment_results.assessment_period_id)'
            ))
            ->join('employees', 'employees.id', '=', 'assessment_results.employee_id')
            ->orderBy('employees.name')
            ->select('assessment_results.*');
    }

    private function row(AssessmentResult $result): array
    {
        $idp = $result->employee?->idpRecommendations->first();

        return [
            $result->assessmentPeriod?->name,
            $result->employee?->employee_number,
            $result->employee?->name,
            $result->employee?->department?->name,
            $result->employee?->position?->name,
            $result->amanah_score,
            $result->kompeten_score,
            $result->harmonis_score,
            $result->loyal_score,
            $result->adaptif_score,
            $result->kolaboratif_score,
            $result->self_score,
            $result->others_score,
            $result->gap_score,
            $result->final_score,
            $result->category,
            $result->talent_mapping_category,
            $idp?->weakest_core_value,
            $idp?->recommendation,
        ];
    }

    private function recordExport(Request $request, string $type, ?string $path, string $status): void
    {
        ReportExport::create([
            'user_id' => $request->user()->id,
            'assessment_period_id' => $request->filled('period_id') ? $request->integer('period_id') : null,
            'report_type' => $type,
            'file_path' => $path,
            'status' => $status,
        ]);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'export_'.$type,
            'module' => 'reports',
            'description' => "Report {$type} export {$status}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
