<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_approvals', function (Blueprint $table): void {
            $table->index(['status', 'scheduled_for'], 'content_approvals_status_scheduled_idx');
            $table->index(['status', 'submitted_at'], 'content_approvals_status_submitted_idx');
            $table->index(['submitted_by_id', 'status'], 'content_approvals_submitter_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['event', 'auditable_type', 'auditable_id', 'occurred_at'], 'audit_logs_dedup_idx');
            $table->index(['actor_user_id', 'occurred_at'], 'audit_logs_actor_occurred_idx');
            $table->index(['auditable_type', 'auditable_id', 'occurred_at'], 'audit_logs_record_history_idx');
        });
    }

    public function down(): void
    {
        Schema::table('content_approvals', function (Blueprint $table): void {
            $table->dropIndex('content_approvals_status_scheduled_idx');
            $table->dropIndex('content_approvals_status_submitted_idx');
            $table->dropIndex('content_approvals_submitter_status_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_dedup_idx');
            $table->dropIndex('audit_logs_actor_occurred_idx');
            $table->dropIndex('audit_logs_record_history_idx');
        });
    }
};
