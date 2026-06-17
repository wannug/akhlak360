<?php

namespace Tests\Feature;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentPeriod;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentAssignmentModuleTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin_hr']);
        $department = Department::create(['name' => 'Operations', 'code' => 'OPS']);
        $supervisorPosition = Position::create(['name' => 'Supervisor', 'level' => 'L3']);
        $staffPosition = Position::create(['name' => 'Staff', 'level' => 'L1']);
        $period = AssessmentPeriod::create([
            'name' => 'Semester 1 2026',
            'semester' => 'Semester 1',
            'year' => 2026,
            'start_date' => '2026-06-16',
            'end_date' => '2026-06-29',
            'status' => 'active',
            'threshold_score' => 3.00,
        ]);

        $supervisor = Employee::create([
            'department_id' => $department->id,
            'position_id' => $supervisorPosition->id,
            'employee_number' => 'SUP-001',
            'name' => 'Supervisor Demo',
            'employment_status' => 'active',
        ]);
        $employee = Employee::create([
            'department_id' => $department->id,
            'position_id' => $staffPosition->id,
            'employee_number' => 'EMP-001',
            'name' => 'Employee Demo',
            'supervisor_id' => $supervisor->id,
            'employment_status' => 'active',
        ]);
        $peer = Employee::create([
            'department_id' => $department->id,
            'position_id' => $staffPosition->id,
            'employee_number' => 'PEER-001',
            'name' => 'Peer Demo',
            'supervisor_id' => $supervisor->id,
            'employment_status' => 'active',
        ]);
        $inactive = Employee::create([
            'department_id' => $department->id,
            'position_id' => $staffPosition->id,
            'employee_number' => 'OLD-001',
            'name' => 'Inactive Demo',
            'employment_status' => 'inactive',
        ]);

        return compact('admin', 'department', 'period', 'supervisor', 'employee', 'peer', 'inactive');
    }

    public function test_admin_can_create_edit_and_delete_pending_assignment(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors', [
                'assessment_period_id' => $fixture['period']->id,
                'assessor_employee_id' => $fixture['peer']->id,
                'assessee_employee_id' => $fixture['employee']->id,
                'assessor_type' => 'peer',
                'status' => 'pending',
            ])
            ->assertRedirect('/assessment-cycle/assign-assessors');

        $assignment = AssessmentAssignment::where('assessor_type', 'peer')->firstOrFail();

        $this->actingAs($fixture['admin'])
            ->put("/assessment-cycle/assign-assessors/{$assignment->id}", [
                'assessment_period_id' => $fixture['period']->id,
                'assessor_employee_id' => $fixture['supervisor']->id,
                'assessee_employee_id' => $fixture['employee']->id,
                'assessor_type' => 'supervisor',
                'status' => 'pending',
            ])
            ->assertRedirect('/assessment-cycle/assign-assessors');

        $this->actingAs($fixture['admin'])
            ->delete("/assessment-cycle/assign-assessors/{$assignment->id}")
            ->assertRedirect('/assessment-cycle/assign-assessors');

        $this->assertDatabaseMissing('assessment_assignments', ['id' => $assignment->id]);
        $this->assertDatabaseHas('audit_logs', ['module' => 'assessment_assignments', 'action' => 'create']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'assessment_assignments', 'action' => 'update']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'assessment_assignments', 'action' => 'delete']);
    }

    public function test_assignment_validation_rules_are_enforced(): void
    {
        $fixture = $this->fixture();

        $base = [
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['employee']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'status' => 'pending',
        ];

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors', $base + ['assessor_type' => 'peer'])
            ->assertSessionHasErrors('assessor_employee_id');

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors', [
                ...$base,
                'assessor_employee_id' => $fixture['peer']->id,
                'assessor_type' => 'self',
            ])
            ->assertSessionHasErrors('assessor_employee_id');

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors', [
                'assessment_period_id' => $fixture['period']->id,
                'assessor_employee_id' => $fixture['inactive']->id,
                'assessee_employee_id' => $fixture['employee']->id,
                'assessor_type' => 'peer',
                'status' => 'pending',
            ])
            ->assertSessionHasErrors('assessor_employee_id');

        AssessmentAssignment::create([
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['peer']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'assessor_type' => 'peer',
            'status' => 'pending',
        ]);

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors', [
                'assessment_period_id' => $fixture['period']->id,
                'assessor_employee_id' => $fixture['peer']->id,
                'assessee_employee_id' => $fixture['employee']->id,
                'assessor_type' => 'peer',
                'status' => 'pending',
            ])
            ->assertSessionHasErrors('assessor_employee_id');
    }

    public function test_submitted_assignment_cannot_be_deleted(): void
    {
        $fixture = $this->fixture();
        $assignment = AssessmentAssignment::create([
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['peer']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'assessor_type' => 'peer',
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->actingAs($fixture['admin'])
            ->delete("/assessment-cycle/assign-assessors/{$assignment->id}")
            ->assertRedirect('/assessment-cycle/assign-assessors');

        $this->assertDatabaseHas('assessment_assignments', ['id' => $assignment->id]);
    }

    public function test_generators_create_expected_assignments(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors/generate-self', [
                'assessment_period_id' => $fixture['period']->id,
            ])
            ->assertRedirect('/assessment-cycle/assign-assessors?assessment_period_id='.$fixture['period']->id);

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors/generate-supervisor', [
                'assessment_period_id' => $fixture['period']->id,
            ])
            ->assertRedirect('/assessment-cycle/assign-assessors?assessment_period_id='.$fixture['period']->id);

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/assign-assessors/generate-subordinate', [
                'assessment_period_id' => $fixture['period']->id,
            ])
            ->assertRedirect('/assessment-cycle/assign-assessors?assessment_period_id='.$fixture['period']->id);

        $this->assertDatabaseHas('assessment_assignments', [
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['employee']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'assessor_type' => 'self',
        ]);
        $this->assertDatabaseHas('assessment_assignments', [
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['supervisor']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'assessor_type' => 'supervisor',
        ]);
        $this->assertDatabaseHas('assessment_assignments', [
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['employee']->id,
            'assessee_employee_id' => $fixture['supervisor']->id,
            'assessor_type' => 'subordinate',
        ]);
        $this->assertDatabaseHas('audit_logs', ['module' => 'assessment_assignments', 'action' => 'generate_self']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'assessment_assignments', 'action' => 'generate_supervisor']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'assessment_assignments', 'action' => 'generate_subordinate']);
    }
}
