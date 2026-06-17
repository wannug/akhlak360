<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin_hr', 'supervisor', 'employee', 'management', 'it_admin'])
                ->default('employee')
                ->after('password');
            $table->string('sso_provider')->nullable()->after('role');
            $table->string('sso_id')->nullable()->after('sso_provider');
            $table->timestamp('last_login_at')->nullable()->after('sso_id');
            $table->index(['role', 'sso_provider']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();

            $table->unique('code');
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('level')->nullable();
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_number')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('employment_status', ['active', 'inactive'])->default('active');
            $table->string('hris_external_id')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['department_id', 'employment_status']);
            $table->index('supervisor_id');
        });

        Schema::create('assessment_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('semester');
            $table->unsignedSmallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->decimal('threshold_score', 4, 2)->default(3.00);
            $table->timestamps();

            $table->index(['year', 'semester', 'status']);
        });

        Schema::create('assessment_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->enum('assessor_type', ['supervisor', 'peer', 'subordinate', 'self']);
            $table->decimal('weight', 5, 2);
            $table->timestamps();

            $table->unique(['assessment_period_id', 'assessor_type']);
        });

        Schema::create('peer_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supervisor_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['assessment_period_id', 'status']);
        });

        Schema::create('assessment_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessor_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('assessee_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('assessor_type', ['supervisor', 'peer', 'subordinate', 'self']);
            $table->enum('status', ['pending', 'submitted'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique([
                'assessment_period_id',
                'assessor_employee_id',
                'assessee_employee_id',
                'assessor_type',
            ], 'assignment_unique');
            $table->index(['assessment_period_id', 'status']);
        });

        Schema::create('assessment_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_assignment_id')->constrained()->cascadeOnDelete();
            $table->string('core_value');
            $table->string('indicator');
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->index(['assessment_assignment_id', 'core_value']);
        });

        Schema::create('assessment_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amanah_score', 4, 2)->nullable();
            $table->decimal('kompeten_score', 4, 2)->nullable();
            $table->decimal('harmonis_score', 4, 2)->nullable();
            $table->decimal('loyal_score', 4, 2)->nullable();
            $table->decimal('adaptif_score', 4, 2)->nullable();
            $table->decimal('kolaboratif_score', 4, 2)->nullable();
            $table->decimal('self_score', 4, 2)->nullable();
            $table->decimal('others_score', 4, 2)->nullable();
            $table->decimal('gap_score', 4, 2)->nullable();
            $table->decimal('final_score', 4, 2)->nullable();
            $table->string('category')->nullable();
            $table->string('talent_mapping_category')->nullable();
            $table->timestamps();

            $table->unique(['assessment_period_id', 'employee_id']);
            $table->index(['assessment_period_id', 'final_score']);
        });

        Schema::create('idp_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('weakest_core_value');
            $table->text('recommendation');
            $table->text('action_plan')->nullable();
            $table->enum('status', ['draft', 'approved', 'in_progress', 'completed'])->default('draft');
            $table->date('due_date')->nullable();
            $table->timestamps();

            $table->index(['assessment_period_id', 'status']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['assessment_reminder', 'system', 'result', 'idp'])->default('system');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index('type');
        });

        Schema::create('hris_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('sync_type', ['import_csv', 'manual_sync']);
            $table->enum('status', ['success', 'failed']);
            $table->unsignedInteger('total_records')->default(0);
            $table->unsignedInteger('success_records')->default(0);
            $table->unsignedInteger('failed_records')->default(0);
            $table->text('message')->nullable();
            $table->foreignId('synced_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['sync_type', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('module');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['module', 'action']);
            $table->index('user_id');
        });

        Schema::create('report_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_period_id')->nullable()->constrained()->nullOnDelete();
            $table->string('report_type');
            $table->string('file_path')->nullable();
            $table->enum('status', ['generated', 'failed'])->default('generated');
            $table->timestamps();

            $table->index(['report_type', 'status']);
            $table->index('assessment_period_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_exports');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('hris_sync_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('idp_recommendations');
        Schema::dropIfExists('assessment_results');
        Schema::dropIfExists('assessment_responses');
        Schema::dropIfExists('assessment_assignments');
        Schema::dropIfExists('peer_approvals');
        Schema::dropIfExists('assessment_weights');
        Schema::dropIfExists('assessment_periods');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'sso_provider']);
            $table->dropColumn(['role', 'sso_provider', 'sso_id', 'last_login_at']);
        });
    }
};
