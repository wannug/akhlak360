<?php

namespace Tests\Feature;

use App\Http\Controllers\Assessment\AssessmentFormController;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentPeriod;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentFormModuleTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin_hr']);
        $assessorUser = User::factory()->create(['role' => 'employee']);
        $otherUser = User::factory()->create(['role' => 'employee']);
        $department = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $period = AssessmentPeriod::create([
            'name' => 'Semester 1 2026',
            'semester' => 'Semester 1',
            'year' => 2026,
            'start_date' => '2026-06-16',
            'end_date' => '2026-06-29',
            'status' => 'active',
            'threshold_score' => 3.00,
        ]);

        $assessor = Employee::create([
            'user_id' => $assessorUser->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-001',
            'name' => 'Assessor Employee',
            'email' => 'assessor@example.com',
            'employment_status' => 'active',
        ]);
        $assessee = Employee::create([
            'user_id' => $otherUser->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-002',
            'name' => 'Assessee Employee',
            'email' => 'assessee@example.com',
            'employment_status' => 'active',
        ]);
        $assignment = AssessmentAssignment::create([
            'assessment_period_id' => $period->id,
            'assessor_employee_id' => $assessor->id,
            'assessee_employee_id' => $assessee->id,
            'assessor_type' => 'peer',
            'status' => 'pending',
        ]);

        return compact('admin', 'assessorUser', 'otherUser', 'department', 'period', 'assessor', 'assessee', 'assignment');
    }

    public function test_user_sees_only_their_pending_assignments(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['assessorUser'])
            ->get('/assessment/pending')
            ->assertOk()
            ->assertSee('Assessee Employee')
            ->assertSee('Fill Assessment');

        $this->actingAs($fixture['otherUser'])
            ->get('/assessment/pending')
            ->assertOk()
            ->assertDontSee('Assessee Employee');
    }

    public function test_assessment_form_requires_all_18_indicators(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['assessorUser'])
            ->post("/assessment/assignments/{$fixture['assignment']->id}/submit", [
                'scores' => [
                    'Amanah' => [0 => 5],
                ],
            ])
            ->assertSessionHasErrors();
    }

    public function test_submit_saves_responses_marks_submitted_notifies_admin_and_audits(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['assessorUser'])
            ->post("/assessment/assignments/{$fixture['assignment']->id}/submit", [
                'scores' => $this->completeScores(),
            ])
            ->assertRedirect('/assessment/pending');

        $this->assertSame('submitted', $fixture['assignment']->fresh()->status);
        $this->assertNotNull($fixture['assignment']->fresh()->submitted_at);
        $this->assertDatabaseCount('assessment_responses', 18);
        $this->assertDatabaseHas('assessment_results', [
            'assessment_period_id' => $fixture['period']->id,
            'employee_id' => $fixture['assessee']->id,
            'final_score' => 4,
            'category' => 'Baik',
        ]);
        $this->assertDatabaseHas('idp_recommendations', [
            'assessment_period_id' => $fixture['period']->id,
            'employee_id' => $fixture['assessee']->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $fixture['admin']->id,
            'title' => 'Assessment Submitted',
            'type' => 'assessment_reminder',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $fixture['assessorUser']->id,
            'action' => 'submit',
            'module' => 'assessment_forms',
        ]);
    }

    public function test_duplicate_submission_is_prevented(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['assessorUser'])
            ->post("/assessment/assignments/{$fixture['assignment']->id}/submit", [
                'scores' => $this->completeScores(),
            ]);

        $this->actingAs($fixture['assessorUser'])
            ->post("/assessment/assignments/{$fixture['assignment']->id}/submit", [
                'scores' => $this->completeScores(),
            ])
            ->assertRedirect('/assessment/pending')
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('assessment_responses', 18);
    }

    public function test_other_user_cannot_fill_assignment(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['otherUser'])
            ->get("/assessment/assignments/{$fixture['assignment']->id}/fill")
            ->assertForbidden();
    }

    private function completeScores(): array
    {
        $scores = [];

        foreach (AssessmentFormController::INDICATORS as $coreValue => $indicators) {
            foreach (array_keys($indicators) as $index) {
                $scores[$coreValue][$index] = 4;
            }
        }

        return $scores;
    }
}
