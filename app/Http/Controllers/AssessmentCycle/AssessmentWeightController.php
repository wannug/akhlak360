<?php

namespace App\Http\Controllers\AssessmentCycle;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentWeight;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentWeightController extends Controller
{
    private const TYPES = ['supervisor', 'peer', 'subordinate', 'self'];

    private const DEFAULTS = [
        'supervisor' => 40,
        'peer' => 20,
        'subordinate' => 30,
        'self' => 10,
    ];

    public function index(Request $request): View
    {
        $periods = AssessmentPeriod::orderByDesc('year')->orderByDesc('start_date')->get();
        $selectedPeriod = $request->filled('assessment_period_id')
            ? AssessmentPeriod::find($request->integer('assessment_period_id'))
            : AssessmentPeriod::active()->first() ?? $periods->first();

        $weights = $this->weightsFor($selectedPeriod);

        return view('assessment-cycle.weights.index', compact('periods', 'selectedPeriod', 'weights'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'assessment_period_id' => ['required', 'exists:assessment_periods,id'],
            'weights' => ['required', 'array'],
            'weights.supervisor' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.peer' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.subordinate' => ['required', 'numeric', 'min:0', 'max:100'],
            'weights.self' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $total = collect($data['weights'])->sum(fn ($weight) => (float) $weight);

        if (round($total, 2) !== 100.00) {
            return back()
                ->withInput()
                ->withErrors(['weights' => 'Total weight must be 100. Current total is '.$total.'.']);
        }

        foreach (self::TYPES as $type) {
            AssessmentWeight::updateOrCreate([
                'assessment_period_id' => $data['assessment_period_id'],
                'assessor_type' => $type,
            ], [
                'weight' => $data['weights'][$type],
            ]);
        }

        $period = AssessmentPeriod::findOrFail($data['assessment_period_id']);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'update',
            'module' => 'assessment_weights',
            'description' => "Updated assessment weights for {$period->name}.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('assessment-cycle.weights.index', ['assessment_period_id' => $period->id])
            ->with('success', 'Assessment weights saved successfully.');
    }

    private function weightsFor(?AssessmentPeriod $period): array
    {
        if (! $period) {
            return self::DEFAULTS;
        }

        $stored = $period->weights()
            ->pluck('weight', 'assessor_type')
            ->map(fn ($weight) => (float) $weight)
            ->all();

        return collect(self::DEFAULTS)
            ->mapWithKeys(fn (int $default, string $type) => [$type => $stored[$type] ?? $default])
            ->all();
    }
}
