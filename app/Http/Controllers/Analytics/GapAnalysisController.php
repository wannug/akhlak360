<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentResult;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GapAnalysisController extends Controller
{
    public function index(Request $request): View
    {
        $periods = AssessmentPeriod::orderByDesc('year')
            ->orderByDesc('start_date')
            ->get();
        $departments = Department::active()
            ->orderBy('name')
            ->get();

        $selectedPeriod = $request->integer('period_id') ?: optional($periods->firstWhere('status', 'active'))->id;
        $selectedDepartment = $request->integer('department_id') ?: null;

        $baseQuery = AssessmentResult::query()
            ->with(['employee.department', 'assessmentPeriod'])
            ->whereNotNull('self_score')
            ->whereNotNull('others_score')
            ->whereNotNull('gap_score')
            ->when($selectedPeriod, fn (Builder $query) => $query->where('assessment_period_id', $selectedPeriod))
            ->when($selectedDepartment, fn (Builder $query) => $query->whereHas(
                'employee',
                fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $selectedDepartment)
            ));

        $summaryResults = (clone $baseQuery)->get();
        $results = (clone $baseQuery)
            ->join('employees', 'employees.id', '=', 'assessment_results.employee_id')
            ->orderBy('employees.name')
            ->select('assessment_results.*')
            ->paginate(15)
            ->withQueryString();

        return view('analytics.gap-analysis', [
            'periods' => $periods,
            'departments' => $departments,
            'selectedPeriod' => $selectedPeriod,
            'selectedDepartment' => $selectedDepartment,
            'results' => $results,
            'averageChart' => [
                'labels' => ['Self Score', 'Others Score'],
                'data' => [
                    $this->roundNullable($summaryResults->avg('self_score')) ?? 0,
                    $this->roundNullable($summaryResults->avg('others_score')) ?? 0,
                ],
            ],
            'gapDistributionChart' => [
                'labels' => ['Self Higher', 'Aligned', 'Self Lower'],
                'data' => [
                    $summaryResults->filter(fn (AssessmentResult $result) => (float) $result->gap_score > 0.50)->count(),
                    $summaryResults->filter(fn (AssessmentResult $result) => (float) $result->gap_score <= 0.50 && (float) $result->gap_score >= -0.50)->count(),
                    $summaryResults->filter(fn (AssessmentResult $result) => (float) $result->gap_score < -0.50)->count(),
                ],
            ],
            'summary' => [
                'total' => $summaryResults->count(),
                'averageGap' => $this->roundNullable($summaryResults->avg('gap_score')),
            ],
        ]);
    }

    private function roundNullable(mixed $value): ?float
    {
        return $value === null ? null : round((float) $value, 2);
    }
}
