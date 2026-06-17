<?php

use App\Http\Controllers\AssessmentCycle\AssessmentAssignmentController;
use App\Http\Controllers\AssessmentCycle\AssessmentPeriodController;
use App\Http\Controllers\AssessmentCycle\PeerApprovalController;
use App\Http\Controllers\AssessmentCycle\AssessmentWeightController;
use App\Http\Controllers\Assessment\AssessmentFormController;
use App\Http\Controllers\Analytics\CoreValueDashboardController;
use App\Http\Controllers\Analytics\GapAnalysisController;
use App\Http\Controllers\AuditCompliance\AuditLogController;
use App\Http\Controllers\AuditCompliance\ComplianceMonitoringController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\IdpTalent\IdpRecommendationController;
use App\Http\Controllers\IdpTalent\TalentMappingController;
use App\Http\Controllers\MasterData\DepartmentController;
use App\Http\Controllers\MasterData\EmployeeController;
use App\Http\Controllers\MasterData\Hris\HrisSyncController;
use App\Http\Controllers\MasterData\PositionController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\SsoSimulationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect('/login');
    }

    return redirect(match (auth()->user()->role) {
        'admin_hr' => '/admin/dashboard',
        'supervisor' => '/supervisor/dashboard',
        'management' => '/management/dashboard',
        'it_admin' => '/it/dashboard',
        default => '/employee/dashboard',
    });
});

Route::get('/sso/simulation', [SsoSimulationController::class, 'show'])
    ->middleware('guest')
    ->name('sso.simulation');

Route::get('/dashboard', function () {
    return redirect(match (auth()->user()->role) {
        'admin_hr' => '/admin/dashboard',
        'supervisor' => '/supervisor/dashboard',
        'management' => '/management/dashboard',
        'it_admin' => '/it/dashboard',
        default => '/employee/dashboard',
    });
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    $placeholder = fn (string $title, string $description) => fn () => view('admin.placeholder', [
        'title' => $title,
        'description' => $description,
    ]);

    Route::get('/admin/dashboard', [DashboardController::class, 'adminHr'])
        ->middleware('role:admin_hr')
        ->name('admin.dashboard');

    Route::get('/supervisor/dashboard', [DashboardController::class, 'supervisor'])
        ->middleware('role:supervisor')
        ->name('supervisor.dashboard');

    Route::get('/employee/dashboard', [DashboardController::class, 'employee'])
        ->middleware('role:employee')
        ->name('employee.dashboard');

    Route::get('/management/dashboard', [DashboardController::class, 'management'])
        ->middleware('role:management')
        ->name('management.dashboard');

    Route::get('/it/dashboard', [DashboardController::class, 'itAdmin'])
        ->middleware('role:it_admin')
        ->name('it.dashboard');

    Route::middleware('role:admin_hr')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/master-data', 'admin.placeholder', [
            'title' => 'Master Data',
            'description' => 'Admin HR dapat mengelola data pegawai, unit kerja, jabatan, dan simulasi import HRIS.',
        ])->name('master-data.index');
        Route::view('/periods', 'admin.placeholder', [
            'title' => 'Periode Penilaian',
            'description' => 'Admin HR dapat mengatur periode, bobot penilai, status siklus, dan threshold skor.',
        ])->name('periods.index');
        Route::view('/assignments', 'admin.placeholder', [
            'title' => 'Assignment Penilaian',
            'description' => 'Admin HR dapat mengelola assignment self, peer, supervisor, dan subordinate.',
        ])->name('assignments.index');
        Route::redirect('/reports', '/reports/export')->name('reports.index');
        Route::redirect('/idp', '/idp-talent/idp-recommendations')->name('idp.index');
    });

    Route::middleware('role:supervisor')->prefix('supervisor')->name('supervisor.')->group(function () {
        Route::view('/peer-approvals', 'admin.placeholder', [
            'title' => 'Persetujuan Peer',
            'description' => 'Supervisor dapat menyetujui, menolak, dan memantau usulan peer reviewer.',
        ])->name('peer-approvals.index');
        Route::view('/assessments', 'admin.placeholder', [
            'title' => 'Form Penilaian Supervisor',
            'description' => 'Supervisor dapat mengisi assessment untuk diri sendiri, rekan, tim, dan bawahan.',
        ])->name('assessments.index');
        Route::view('/team', 'admin.placeholder', [
            'title' => 'Dashboard Tim',
            'description' => 'Supervisor dapat melihat progres dan ringkasan hasil penilaian tim.',
        ])->name('team.index');
    });

    Route::middleware('role:employee')->prefix('employee')->name('employee.')->group(function () {
        Route::view('/assessments', 'admin.placeholder', [
            'title' => 'Form Penilaian Saya',
            'description' => 'Employee dapat mengisi assessment yang ditugaskan.',
        ])->name('assessments.index');
        Route::view('/results', 'admin.placeholder', [
            'title' => 'Hasil Personal',
            'description' => 'Employee dapat melihat ringkasan hasil pribadi dan rekomendasi pengembangan.',
        ])->name('results.index');
    });

    Route::middleware('role:management')->prefix('management')->name('management.')->group(function () {
        Route::view('/analytics', 'admin.placeholder', [
            'title' => 'Analitik Manajemen',
            'description' => 'Management dapat melihat ringkasan strategis, distribusi skor, dan talent mapping.',
        ])->name('analytics.index');
        Route::redirect('/reports', '/reports/export')->name('reports.index');
    });

    Route::middleware('role:it_admin')->prefix('it')->name('it.')->group(function () {
        Route::view('/hris-sync-logs', 'admin.placeholder', [
            'title' => 'HRIS Sync Logs',
            'description' => 'IT Admin dapat melihat log simulasi import CSV dan sinkronisasi HRIS.',
        ])->name('hris-sync-logs.index');
        Route::view('/audit-logs', 'admin.placeholder', [
            'title' => 'Audit Logs',
            'description' => 'IT Admin dapat melihat aktivitas login, perubahan data, export, dan aksi sistem.',
        ])->name('audit-logs.index');
        Route::view('/settings', 'admin.placeholder', [
            'title' => 'System Settings',
            'description' => 'IT Admin dapat melihat pengaturan sistem dan simulasi konfigurasi integrasi.',
        ])->name('settings.index');
    });

    Route::middleware('role:admin_hr')->prefix('master-data')->name('master-data.')->group(function () use ($placeholder) {
        Route::resource('departments', DepartmentController::class)->except('show');
        Route::resource('positions', PositionController::class)->except('show');
        Route::resource('employees', EmployeeController::class)->except('show');
    });

    Route::middleware('role:admin_hr,it_admin')->prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/hris-sync', [HrisSyncController::class, 'index'])->name('hris-sync.index');
        Route::post('/hris-sync/import', [HrisSyncController::class, 'import'])->name('hris-sync.import');
        Route::post('/hris-sync/manual', [HrisSyncController::class, 'manualSync'])->name('hris-sync.manual');
    });

    Route::middleware('role:admin_hr')->prefix('assessment-cycle')->name('assessment-cycle.')->group(function () use ($placeholder) {
        Route::post('/periods/{period}/recalculate', [AssessmentPeriodController::class, 'recalculate'])->name('periods.recalculate');
        Route::resource('periods', AssessmentPeriodController::class)->except('show');
        Route::get('/weights', [AssessmentWeightController::class, 'index'])->name('weights.index');
        Route::post('/weights', [AssessmentWeightController::class, 'update'])->name('weights.update');
        Route::get('/assign-assessors', [AssessmentAssignmentController::class, 'index'])->name('assign-assessors.index');
        Route::get('/assign-assessors/create', [AssessmentAssignmentController::class, 'create'])->name('assign-assessors.create');
        Route::post('/assign-assessors', [AssessmentAssignmentController::class, 'store'])->name('assign-assessors.store');
        Route::get('/assign-assessors/{assignment}/edit', [AssessmentAssignmentController::class, 'edit'])->name('assign-assessors.edit');
        Route::put('/assign-assessors/{assignment}', [AssessmentAssignmentController::class, 'update'])->name('assign-assessors.update');
        Route::delete('/assign-assessors/{assignment}', [AssessmentAssignmentController::class, 'destroy'])->name('assign-assessors.destroy');
        Route::post('/assign-assessors/generate-self', [AssessmentAssignmentController::class, 'generateSelf'])->name('assign-assessors.generate-self');
        Route::post('/assign-assessors/generate-supervisor', [AssessmentAssignmentController::class, 'generateSupervisor'])->name('assign-assessors.generate-supervisor');
        Route::post('/assign-assessors/generate-subordinate', [AssessmentAssignmentController::class, 'generateSubordinate'])->name('assign-assessors.generate-subordinate');
    });

    Route::middleware('role:admin_hr,supervisor')->prefix('assessment-cycle')->name('assessment-cycle.')->group(function () {
        Route::get('/peer-approval', [PeerApprovalController::class, 'index'])->name('peer-approval.index');
        Route::post('/peer-approval', [PeerApprovalController::class, 'store'])->name('peer-approval.store');
        Route::patch('/peer-approval/{peerApproval}/approve', [PeerApprovalController::class, 'approve'])->name('peer-approval.approve');
        Route::patch('/peer-approval/{peerApproval}/reject', [PeerApprovalController::class, 'reject'])->name('peer-approval.reject');
    });

    Route::middleware('role:supervisor,employee')->prefix('assessment')->name('assessment.')->group(function () use ($placeholder) {
        Route::get('/pending', [AssessmentFormController::class, 'pending'])->name('pending.index');
        Route::get('/fill', [AssessmentFormController::class, 'redirectToPending'])->name('fill.index');
        Route::get('/assignments/{assignment}/fill', [AssessmentFormController::class, 'show'])->name('fill.show');
        Route::post('/assignments/{assignment}/submit', [AssessmentFormController::class, 'submit'])->name('submit');
        Route::get('/results', $placeholder('Results', 'Lihat hasil penilaian dan ringkasan skor.'))->name('results.index');
    });

    Route::middleware('role:admin_hr,management')->prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/core-value-dashboard', [CoreValueDashboardController::class, 'index'])->name('core-values.index');
    });

    Route::middleware('role:admin_hr,supervisor,management')->prefix('analytics')->name('analytics.')->group(function () use ($placeholder) {
        Route::get('/gap-analysis', [GapAnalysisController::class, 'index'])->name('gap-analysis.index');
        Route::get('/department-distribution', $placeholder('Department Distribution', 'Distribusi skor berdasarkan departemen.'))->name('department-distribution.index');
        Route::get('/semester-trend', $placeholder('Semester Trend', 'Tren skor antar semester.'))->name('semester-trend.index');
        Route::get('/below-threshold', $placeholder('Below Threshold', 'Daftar pegawai dengan skor di bawah threshold periode.'))->name('below-threshold.index');
    });

    Route::middleware('role:admin_hr,supervisor,employee,management')->prefix('idp-talent')->name('idp-talent.')->group(function () use ($placeholder) {
        Route::get('/idp-recommendations', [IdpRecommendationController::class, 'index'])->name('idp-recommendations.index');
        Route::get('/idp-recommendations/{idpRecommendation}/edit', [IdpRecommendationController::class, 'edit'])->name('idp-recommendations.edit');
        Route::put('/idp-recommendations/{idpRecommendation}', [IdpRecommendationController::class, 'update'])->name('idp-recommendations.update');
        Route::get('/talent-mapping', [TalentMappingController::class, 'index'])->name('talent-mapping.index');
        Route::get('/talent-mapping/export', [TalentMappingController::class, 'export'])->name('talent-mapping.export');
    });

    Route::middleware('role:admin_hr,management')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/export', [ReportController::class, 'index'])->name('export.index');
        Route::get('/export/csv', [ReportController::class, 'csv'])->name('export.csv');
        Route::get('/export/excel', [ReportController::class, 'excel'])->name('export.excel');
        Route::get('/export/pdf', [ReportController::class, 'pdf'])->name('export.pdf');
        Route::get('/history', [ReportController::class, 'history'])->name('history.index');
    });

    Route::middleware('role:admin_hr,supervisor,employee,management,it_admin')->group(function () {
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/navbar', [NotificationController::class, 'navbar'])->name('notifications.navbar');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    });

    Route::middleware('role:it_admin')->prefix('audit-compliance')->name('audit-compliance.')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('role:admin_hr,it_admin')->prefix('audit-compliance')->name('audit-compliance.')->group(function () {
        Route::get('/compliance-monitoring', [ComplianceMonitoringController::class, 'index'])->name('compliance-monitoring.index');
        Route::post('/compliance-monitoring/reminders', [ComplianceMonitoringController::class, 'sendReminders'])->name('compliance-monitoring.reminders');
    });

    Route::middleware('role:it_admin')->group(function () use ($placeholder) {
        Route::get('/system-settings', $placeholder('System Settings', 'Kelola simulasi konfigurasi sistem dan integrasi.'))->name('system-settings.index');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
