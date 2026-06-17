<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class HrisSyncModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_hris_sync_page_imports_csv_and_logs_activity(): void
    {
        $admin = User::factory()->create(['role' => 'admin_hr']);
        $csv = implode("\n", [
            'employee_number,name,email,department_code,department,position,position_level,supervisor_employee_number,employment_status,hris_external_id',
            'SUP-HRIS,Supervisor HRIS,supervisor.hris@example.com,OPS,Operations,Supervisor,L3,,active,EXT-SUP',
            'EMP-HRIS,Employee HRIS,employee.hris@example.com,OPS,Operations,Staff,L1,SUP-HRIS,active,EXT-EMP',
        ]);

        $this->actingAs($admin)
            ->get('/master-data/hris-sync')
            ->assertOk()
            ->assertSee('HRIS Sync')
            ->assertSee('Import Employee CSV');

        $this->actingAs($admin)
            ->post('/master-data/hris-sync/import', [
                'csv_file' => UploadedFile::fake()->createWithContent('employees.csv', $csv),
            ])
            ->assertRedirect('/master-data/hris-sync')
            ->assertSessionHas('success');

        $department = Department::where('code', 'OPS')->firstOrFail();
        $position = Position::where('name', 'Staff')->firstOrFail();
        $supervisor = Employee::where('employee_number', 'SUP-HRIS')->firstOrFail();

        $this->assertDatabaseHas('employees', [
            'employee_number' => 'EMP-HRIS',
            'name' => 'Employee HRIS',
            'department_id' => $department->id,
            'position_id' => $position->id,
            'supervisor_id' => $supervisor->id,
            'hris_external_id' => 'EXT-EMP',
        ]);
        $this->assertDatabaseHas('hris_sync_logs', [
            'sync_type' => 'import_csv',
            'status' => 'success',
            'total_records' => 2,
            'success_records' => 2,
            'failed_records' => 0,
            'synced_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'module' => 'hris_sync',
            'action' => 'import_csv',
        ]);
    }

    public function test_manual_sync_logs_activity(): void
    {
        $it = User::factory()->create(['role' => 'it_admin']);

        $this->actingAs($it)
            ->post('/master-data/hris-sync/manual')
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('hris_sync_logs', [
            'sync_type' => 'manual_sync',
            'status' => 'success',
            'synced_by' => $it->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $it->id,
            'module' => 'hris_sync',
            'action' => 'manual_sync',
        ]);
    }
}
