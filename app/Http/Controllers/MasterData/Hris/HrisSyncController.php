<?php

namespace App\Http\Controllers\MasterData\Hris;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Employee;
use App\Models\HrisSyncLog;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class HrisSyncController extends Controller
{
    public function index(): View
    {
        return view('master-data.hris-sync.index', [
            'logs' => HrisSyncLog::with('syncedBy')->latest()->paginate(10),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
        ]);

        $rows = $this->readCsv($request->file('csv_file')->getRealPath());
        $success = 0;
        $failed = 0;
        $messages = [];

        foreach ($rows as $index => $row) {
            try {
                $this->syncRow($row);
                $success++;
            } catch (\Throwable $exception) {
                $failed++;
                $messages[] = 'Row '.($index + 2).': '.$exception->getMessage();
            }
        }

        $status = $failed === 0 ? 'success' : 'failed';
        $message = $messages === []
            ? 'CSV import completed successfully.'
            : implode(' | ', array_slice($messages, 0, 5));

        HrisSyncLog::create([
            'sync_type' => 'import_csv',
            'status' => $status,
            'total_records' => count($rows),
            'success_records' => $success,
            'failed_records' => $failed,
            'message' => $message,
            'synced_by' => $request->user()?->id,
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'import_csv',
            'module' => 'hris_sync',
            'description' => "Imported HRIS CSV with {$success} success and {$failed} failed records.",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('master-data.hris-sync.index')
            ->with($failed === 0 ? 'success' : 'warning', $message);
    }

    public function manualSync(Request $request): RedirectResponse
    {
        HrisSyncLog::create([
            'sync_type' => 'manual_sync',
            'status' => 'success',
            'total_records' => Employee::count(),
            'success_records' => Employee::count(),
            'failed_records' => 0,
            'message' => 'Manual HRIS sync simulation completed.',
            'synced_by' => $request->user()?->id,
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'manual_sync',
            'module' => 'hris_sync',
            'description' => 'Manual HRIS sync simulation completed.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Manual HRIS sync simulation completed.');
    }

    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);

        if (! $headers) {
            throw new \RuntimeException('CSV file is empty.');
        }

        $headers = array_map(fn (string $header) => strtolower(trim($header)), $headers);
        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows[] = array_combine($headers, array_slice(array_pad($values, count($headers), null), 0, count($headers)));
        }

        fclose($handle);

        return $rows;
    }

    private function syncRow(array $row): void
    {
        $employeeNumber = trim((string) Arr::get($row, 'employee_number', ''));
        $name = trim((string) Arr::get($row, 'name', ''));
        $departmentName = trim((string) (Arr::get($row, 'department') ?: Arr::get($row, 'department_name', '')));
        $departmentCode = trim((string) Arr::get($row, 'department_code', ''));

        if ($employeeNumber === '' || $name === '' || $departmentName === '') {
            throw new \InvalidArgumentException('employee_number, name, and department are required.');
        }

        $departmentLookup = $departmentCode !== '' ? ['code' => $departmentCode] : ['name' => $departmentName];
        $department = Department::firstOrCreate($departmentLookup, [
            'code' => $departmentCode !== '' ? $departmentCode : null,
            'name' => $departmentName,
            'is_active' => true,
        ]);

        if ($department->name !== $departmentName) {
            $department->update(['name' => $departmentName, 'is_active' => true]);
        }

        $position = null;
        $positionName = trim((string) Arr::get($row, 'position', ''));

        if ($positionName !== '') {
            $position = Position::firstOrCreate(
                ['name' => $positionName],
                ['level' => trim((string) Arr::get($row, 'position_level', '')) ?: null],
            );
        }

        $supervisor = null;
        $supervisorNumber = trim((string) Arr::get($row, 'supervisor_employee_number', ''));

        if ($supervisorNumber !== '' && $supervisorNumber !== $employeeNumber) {
            $supervisor = Employee::where('employee_number', $supervisorNumber)->first();
        }

        Employee::updateOrCreate(
            ['employee_number' => $employeeNumber],
            [
                'department_id' => $department->id,
                'position_id' => $position?->id,
                'supervisor_id' => $supervisor?->id,
                'name' => $name,
                'email' => trim((string) Arr::get($row, 'email', '')) ?: null,
                'employment_status' => in_array(Arr::get($row, 'employment_status'), ['active', 'inactive'], true)
                    ? Arr::get($row, 'employment_status')
                    : 'active',
                'hris_external_id' => trim((string) Arr::get($row, 'hris_external_id', '')) ?: null,
                'last_synced_at' => now(),
            ],
        );
    }
}
