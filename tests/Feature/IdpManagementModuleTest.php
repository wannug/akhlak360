<?php

namespace Tests\Feature;

use App\Models\AssessmentPeriod;
use App\Models\Department;
use App\Models\Employee;
use App\Models\IdpRecommendation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdpManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_hr_can_filter_edit_idp_and_updates_are_audited(): void
    {
        $fixture = $this->fixture();
        $recommendation = $this->idp($fixture['period'], $fixture['employee'], 'Amanah');

        $this->actingAs($fixture['admin'])
            ->get("/idp-talent/idp-recommendations?period_id={$fixture['period']->id}&department_id={$fixture['department']->id}&status=draft")
            ->assertOk()
            ->assertSee('IDP Recommendations')
            ->assertSee('EMP-IDP-001')
            ->assertSee('Coaching accountability');

        $this->actingAs($fixture['admin'])
            ->get("/idp-talent/idp-recommendations/{$recommendation->id}/edit")
            ->assertOk()
            ->assertSee('Edit IDP Recommendation');

        $this->actingAs($fixture['admin'])
            ->put("/idp-talent/idp-recommendations/{$recommendation->id}", [
                'action_plan' => 'Monthly coaching and accountability review.',
                'due_date' => '2026-07-31',
                'status' => 'in_progress',
            ])
            ->assertRedirect('/idp-talent/idp-recommendations');

        $this->assertDatabaseHas('idp_recommendations', [
            'id' => $recommendation->id,
            'action_plan' => 'Monthly coaching and accountability review.',
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $fixture['admin']->id,
            'module' => 'idp_recommendations',
            'action' => 'update',
        ]);
    }

    public function test_employee_only_sees_their_own_idp(): void
    {
        $fixture = $this->fixture();
        $otherUser = User::factory()->create(['role' => 'employee']);
        $otherEmployee = Employee::create([
            'user_id' => $otherUser->id,
            'department_id' => $fixture['department']->id,
            'employee_number' => 'EMP-IDP-002',
            'name' => 'Other Employee',
            'email' => 'other.idp@example.com',
            'employment_status' => 'active',
        ]);

        $this->idp($fixture['period'], $fixture['employee'], 'Kompeten');
        $this->idp($fixture['period'], $otherEmployee, 'Adaptif');

        $this->actingAs($fixture['employeeUser'])
            ->get('/idp-talent/idp-recommendations')
            ->assertOk()
            ->assertSee('EMP-IDP-001')
            ->assertDontSee('EMP-IDP-002')
            ->assertDontSee('Edit IDP Recommendation');
    }

    public function test_management_sees_summary_only(): void
    {
        $fixture = $this->fixture();
        $management = User::factory()->create(['role' => 'management']);
        $this->idp($fixture['period'], $fixture['employee'], 'Harmonis');

        $this->actingAs($management)
            ->get('/idp-talent/idp-recommendations')
            ->assertOk()
            ->assertSee('IDP Summary')
            ->assertSee('Total IDP')
            ->assertDontSee('EMP-IDP-001')
            ->assertDontSee('Communication training');
    }

    public function test_non_admin_cannot_edit_idp(): void
    {
        $fixture = $this->fixture();
        $recommendation = $this->idp($fixture['period'], $fixture['employee'], 'Loyal');

        $this->actingAs($fixture['employeeUser'])
            ->get("/idp-talent/idp-recommendations/{$recommendation->id}/edit")
            ->assertForbidden();
    }

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin_hr']);
        $employeeUser = User::factory()->create(['role' => 'employee']);
        $department = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $period = AssessmentPeriod::create([
            'name' => 'Semester 1 2026',
            'semester' => 'Semester 1',
            'year' => 2026,
            'start_date' => '2026-06-17',
            'end_date' => '2026-06-30',
            'status' => 'active',
            'threshold_score' => 3.00,
        ]);
        $employee = Employee::create([
            'user_id' => $employeeUser->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-IDP-001',
            'name' => 'IDP Employee',
            'email' => 'idp.employee@example.com',
            'employment_status' => 'active',
        ]);

        return compact('admin', 'employeeUser', 'department', 'period', 'employee');
    }

    private function idp(AssessmentPeriod $period, Employee $employee, string $coreValue): IdpRecommendation
    {
        $mapping = [
            'Amanah' => 'Coaching accountability, commitment management, and ethical responsibility.',
            'Kompeten' => 'Technical training, continuous learning plan, and knowledge sharing session.',
            'Harmonis' => 'Communication training, conflict management, and teamwork development.',
            'Loyal' => 'Corporate values alignment, BUMN culture program, and organizational commitment.',
            'Adaptif' => 'Change management workshop, innovation challenge, and problem-solving training.',
            'Kolaboratif' => 'Cross-functional project assignment, collaboration workshop, and information-sharing practice.',
        ];

        return IdpRecommendation::create([
            'assessment_period_id' => $period->id,
            'employee_id' => $employee->id,
            'weakest_core_value' => $coreValue,
            'recommendation' => $mapping[$coreValue],
            'action_plan' => 'Initial action plan.',
            'status' => 'draft',
            'due_date' => '2026-07-31',
        ]);
    }
}
