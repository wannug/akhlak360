<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peer_approvals', function (Blueprint $table) {
            $table->foreignId('peer_employee_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->index(['assessment_period_id', 'employee_id', 'peer_employee_id'], 'peer_approval_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('peer_approvals', function (Blueprint $table) {
            $table->dropIndex('peer_approval_lookup');
            $table->dropConstrainedForeignId('peer_employee_id');
        });
    }
};
