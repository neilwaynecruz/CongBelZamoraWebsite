<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table): void {
            $table->string('priority', 32)->default('normal')->after('status')->index();
            $table->string('office_unit', 120)->nullable()->after('inquiry_category')->index();
            $table->timestamp('assigned_at')->nullable()->after('assigned_officer_id')->index();
            $table->timestamp('first_response_due_at')->nullable()->after('privacy_consent_at')->index();
            $table->timestamp('first_responded_at')->nullable()->after('responded_at')->index();
            $table->timestamp('last_internal_activity_at')->nullable()->after('resolved_at')->index();
            $table->timestamp('closed_at')->nullable()->after('last_internal_activity_at')->index();
            $table->string('closed_reason', 120)->nullable()->after('closed_at');
            $table->timestamp('overdue_reminder_sent_at')->nullable()->after('closed_reason')->index();
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table): void {
            $table->dropColumn([
                'priority',
                'office_unit',
                'assigned_at',
                'first_response_due_at',
                'first_responded_at',
                'last_internal_activity_at',
                'closed_at',
                'closed_reason',
                'overdue_reminder_sent_at',
            ]);
        });
    }
};
