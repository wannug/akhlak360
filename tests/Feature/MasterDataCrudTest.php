<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataCrudTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin_hr',
        ]);
    }

    public function test_department_crud_deactivates_when_related_employees_exist(): void
    {
        $admin = $this->admin();
        $department = Department::create(['code' => 'OPS', 'name' => 'Operations']);
        $position = Position::create(['name' => 'Staff', 'level' => 'L1']);

        Employee::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_number' => 'EMP-001',
            'name' => 'Demo Employee',
            'email' => 'demo.employee@example.com',
            'employment_status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get('/master-data/departments')
            ->assertOk()
            ->assertSee('Operations');

        $this->actingAs($admin)
            ->post('/master-data/departments', [
                'code' => 'HC',
                'name' => 'Human Capital',
            ])
            ->assertRedirect('/master-data/departments');

        $this->assertDatabaseHas('departments', ['code' => 'HC']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'departments', 'action' => 'create']);

        $this->actingAs($admin)
            ->delete("/master-data/departments/{$department->id}")
            ->assertRedirect('/master-data/departments');

        $this->assertFalse($department->fresh()->is_active);
        $this->assertDatabaseHas('audit_logs', ['module' => 'departments', 'action' => 'deactivate']);
    }

    public function test_position_crud_creates_updates_and_deletes_with_audit_logs(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post('/master-data/positions', [
                'name' => 'Supervisor',
                'level' => 'L3',
            ])
            ->assertRedirect('/master-data/positions');

        $position = Position::where('name', 'Supervisor')->firstOrFail();

        $this->actingAs($admin)
            ->put("/master-data/positions/{$position->id}", [
                'name' => 'Senior Supervisor',
                'level' => 'L3',
            ])
            ->assertRedirect('/master-data/positions');

        $this->actingAs($admin)
            ->delete("/master-data/positions/{$position->id}")
            ->assertRedirect('/master-data/positions');

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
        $this->assertDatabaseHas('audit_logs', ['module' => 'positions', 'action' => 'create']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'positions', 'action' => 'update']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'positions', 'action' => 'delete']);
    }

    public function test_employee_crud_filters_updates_and_prevents_self_supervisor(): void
    {
        $admin = $this->admin();
        $department = Department::create(['code' => 'IT', 'name' => 'IT']);
        $position = Position::create(['name' => 'Staff', 'level' => 'L1']);
        $linkedUser = User::factory()->create(['role' => 'employee']);

        $supervisor = Employee::create([
            'department_id' => $department->id,
            'position_id' => $position->id,
            'employee_number' => 'SUP-001',
            'name' => 'Supervisor Demo',
            'email' => 'supervisor.demo@example.com',
            'employment_status' => 'active',
        ]);

        $this->actingAs($admin)
            ->post('/master-data/employees', [
                'employee_number' => 'EMP-100',
                'name' => 'Employee Demo',
                'email' => 'employee.demo@example.com',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'supervisor_id' => $supervisor->id,
                'employment_status' => 'active',
                'user_id' => $linkedUser->id,
                'hris_external_id' => 'HRIS-EMP-100',
            ])
            ->assertRedirect('/master-data/employees');

        $employee = Employee::where('employee_number', 'EMP-100')->firstOrFail();

        $this->actingAs($admin)
            ->get('/master-data/employees?search=Employee+Demo&department_id='.$department->id.'&employment_status=active')
            ->assertOk()
            ->assertSee('Employee Demo');

        $this->actingAs($admin)
            ->put("/master-data/employees/{$employee->id}", [
                'employee_number' => 'EMP-100',
                'name' => 'Employee Demo Updated',
                'email' => 'employee.updated@example.com',
                'department_id' => $department->id,
                'position_id' => $position->id,
                'supervisor_id' => $employee->id,
                'employment_status' => 'active',
                'user_id' => $linkedUser->id,
                'hris_external_id' => 'HRIS-EMP-100',
            ])
            ->assertSessionHasErrors('supervisor_id');

        $this->actingAs($admin)
            ->delete("/master-data/employees/{$employee->id}")
            ->assertRedirect('/master-data/employees');

        $this->assertSame('inactive', $employee->fresh()->employment_status);
        $this->assertDatabaseHas('audit_logs', ['module' => 'employees', 'action' => 'create']);
        $this->assertDatabaseHas('audit_logs', ['module' => 'employees', 'action' => 'deactivate']);
    }
}
