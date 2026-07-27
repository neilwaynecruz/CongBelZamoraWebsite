<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table): void {
            $table->index(['anonymized_at', 'retention_expires_at'], 'inquiries_retention_scan_idx');
            $table->index(['status', 'created_at'], 'inquiries_status_created_idx');
            $table->index(['assigned_officer_id', 'status'], 'inquiries_officer_status_idx');
            $table->index('created_at', 'inquiries_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(['admin_role', 'is_active'], 'users_role_active_idx');
        });

        Schema::table('source_records', function (Blueprint $table): void {
            $table->index(['source_tier', 'next_review_at'], 'source_records_tier_review_idx');
            $table->index(['content_owner_id', 'next_review_at'], 'source_records_owner_review_idx');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table): void {
            $table->dropIndex('inquiries_retention_scan_idx');
            $table->dropIndex('inquiries_status_created_idx');
            $table->dropIndex('inquiries_officer_status_idx');
            $table->dropIndex('inquiries_created_at_idx');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_active_idx');
        });

        Schema::table('source_records', function (Blueprint $table): void {
            $table->dropIndex('source_records_tier_review_idx');
            $table->dropIndex('source_records_owner_review_idx');
        });
    }
};
