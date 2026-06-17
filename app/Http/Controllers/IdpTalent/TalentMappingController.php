<?php

namespace App\Http\Controllers\IdpTalent;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentResult;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class TalentMappingController extends Controller
{
    private const CATEGORIES = [
        'High Potential',
        'Solid Contributor',
        'Core Contributor',
        'Need Development',
    ];

    public function index(Request $request): View
    {
        $periods = AssessmentPeriod::orderByDesc('year')->orderByDesc('start_date')->get();
        $departments = Department::active()->orderBy('name')->get();
        $selectedPeriod = $request->integer('period_id') ?: optional($periods->firstWhere('status', 'active'))->id;
        $selectedDepartment = $request->integer('department_id') ?: null;
        $allResults = $this->filteredQuery($selectedPeriod, $selectedDepartment)->get();
        $results = $this->filteredQuery($selectedPeriod, $selectedDepartment)
            ->paginate(15)
            ->withQueryString();

        return view('idp-talent.talent-mapping.index', [
            'periods' => $periods,
            'departments' => $departments,
            'selectedPeriod' => $selectedPeriod,
            'selectedDepartment' => $selectedDepartment,
            'results' => $results,
            'categoryCounts' => $this->categoryCounts($allResults),
            'categoryChart' => [
                'labels' => self::CATEGORIES,
                'data' => $this->categoryCounts($allResults)->values()->all(),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $selectedPeriod = $request->integer('period_id') ?: null;
        $selectedDepartment = $request->integer('department_id') ?: null;
        $filename = 'talent-mapping-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($selectedPeriod, $selectedDepartment): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'employee_name',
                'department',
                'period',
                'final_score',
                'gap_score',
                'talent_mapping_category',
                'idp_status',
            ]);

            $this->filteredQuery($selectedPeriod, $selectedDepartment)
                ->chunk(100, function (Collection $results) use ($handle): void {
                    foreach ($results as $result) {
                        fputcsv($handle, [
                            $result->employee?->name,
                            $result->employee?->department?->name,
                            $result->assessmentPeriod?->name,
                            $result->final_score,
                            $result->gap_score,
                            $result->talent_mapping_category,
                            $result->employee?->idpRecommendations->first()?->status ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function filteredQuery(?int $periodId, ?int $departmentId): Builder
    {
        return AssessmentResult::query()
            ->with(['assessmentPeriod', 'employee.department'])
            ->with(['employee.idpRecommendations' => fn ($query) => $query->when($periodId, fn ($query) => $query->where('assessment_period_id', $periodId))])
            ->whereNotNull('talent_mapping_category')
            ->when($periodId, fn (Builder $query) => $query->where('assessment_period_id', $periodId))
            ->when($departmentId, fn (Builder $query) => $query->whereHas(
                'employee',
                fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId)
            ))
            ->join('employees', 'employees.id', '=', 'assessment_results.employee_id')
            ->orderBy('employees.name')
            ->select('assessment_results.*');
    }

    private function categoryCounts(Collection $results): Collection
    {
        $grouped = $results->groupBy('talent_mapping_category')->map->count();

        return collect(self::CATEGORIES)
            ->mapWithKeys(fn (string $category) => [$category => $grouped[$category] ?? 0]);
    }
}
