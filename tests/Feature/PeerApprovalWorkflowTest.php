<?php

namespace Tests\Feature;

use App\Models\AssessmentPeriod;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PeerApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeerApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(): array
    {
        $admin = User::factory()->create(['role' => 'admin_hr']);
        $supervisorUser = User::factory()->create(['role' => 'supervisor']);
        $department = Department::create(['name' => 'Operations', 'code' => 'OPS']);

        $supervisor = Employee::create([
            'user_id' => $supervisorUser->id,
            'department_id' => $department->id,
            'employee_number' => 'SUP-001',
            'name' => 'Supervisor Demo',
            'email' => 'supervisor.demo@example.com',
            'employment_status' => 'active',
        ]);

        $employee = Employee::create([
            'department_id' => $department->id,
            'employee_number' => 'EMP-001',
            'name' => 'Employee Demo',
            'email' => 'employee.demo@example.com',
            'supervisor_id' => $supervisor->id,
            'employment_status' => 'active',
        ]);

        $peer = Employee::create([
            'department_id' => $department->id,
            'employee_number' => 'PEER-001',
            'name' => 'Peer Demo',
            'email' => 'peer.demo@example.com',
            'supervisor_id' => $supervisor->id,
            'employment_status' => 'active',
        ]);

        $period = AssessmentPeriod::create([
            'name' => 'Semester 1 2026',
            'semester' => 'Semester 1',
            'year' => 2026,
            'start_date' => '2026-06-16',
            'end_date' => '2026-06-29',
            'status' => 'active',
            'threshold_score' => 3.00,
        ]);

        return compact('admin', 'supervisorUser', 'supervisor', 'employee', 'peer', 'period');
    }

    public function test_admin_hr_can_propose_peer_assessor_for_active_period(): void
    {
        $fixture = $this->fixture();

        $this->actingAs($fixture['admin'])
            ->post('/assessment-cycle/peer-approval', [
                'assessment_period_id' => $fixture['period']->id,
                'employee_id' => $fixture['employee']->id,
                'peer_employee_id' => $fixture['peer']->id,
                'notes' => 'Peer knows cross-functional work.',
            ])
            ->assertRedirect('/assessment-cycle/peer-approval');

        $this->assertDatabaseHas('peer_approvals', [
            'assessment_period_id' => $fixture['period']->id,
            'employee_id' => $fixture['employee']->id,
            'peer_employee_id' => $fixture['peer']->id,
            'supervisor_employee_id' => $fixture['supervisor']->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'peer_approvals',
            'action' => 'propose',
        ]);
    }

    public function test_supervisor_can_approve_peer_and_create_assignment(): void
    {
        $fixture = $this->fixture();
        $approval = PeerApproval::create([
            'assessment_period_id' => $fixture['period']->id,
            'employee_id' => $fixture['employee']->id,
            'peer_employee_id' => $fixture['peer']->id,
            'supervisor_employee_id' => $fixture['supervisor']->id,
            'status' => 'pending',
        ]);

        $this->actingAs($fixture['supervisorUser'])
            ->get('/assessment-cycle/peer-approval')
            ->assertOk()
            ->assertSee('Employee Demo')
            ->assertSee('Peer Demo');

        $this->actingAs($fixture['supervisorUser'])
            ->patch("/assessment-cycle/peer-approval/{$approval->id}/approve", [
                'notes' => 'Approved.',
            ])
            ->assertRedirect('/assessment-cycle/peer-approval');

        $this->assertDatabaseHas('peer_approvals', [
            'id' => $approval->id,
            'status' => 'approved',
            'notes' => 'Approved.',
        ]);
        $this->assertDatabaseHas('assessment_assignments', [
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['peer']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'assessor_type' => 'peer',
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'peer_approvals',
            'action' => 'approve',
        ]);
    }

    public function test_supervisor_can_reject_peer_with_notes(): void
    {
        $fixture = $this->fixture();
        $approval = PeerApproval::create([
            'assessment_period_id' => $fixture['period']->id,
            'employee_id' => $fixture['employee']->id,
            'peer_employee_id' => $fixture['peer']->id,
            'supervisor_employee_id' => $fixture['supervisor']->id,
            'status' => 'pending',
        ]);

        $this->actingAs($fixture['supervisorUser'])
            ->patch("/assessment-cycle/peer-approval/{$approval->id}/reject", [
                'notes' => 'Reviewer has conflict of interest.',
            ])
            ->assertRedirect('/assessment-cycle/peer-approval');

        $this->assertDatabaseHas('peer_approvals', [
            'id' => $approval->id,
            'status' => 'rejected',
            'notes' => 'Reviewer has conflict of interest.',
        ]);
        $this->assertDatabaseMissing('assessment_assignments', [
            'assessment_period_id' => $fixture['period']->id,
            'assessor_employee_id' => $fixture['peer']->id,
            'assessee_employee_id' => $fixture['employee']->id,
            'assessor_type' => 'peer',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'module' => 'peer_approvals',
            'action' => 'reject',
        ]);
    }
}
