<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('content_approval_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('approvable');
            $table->unsignedInteger('version_number')->default(1);
            $table->unsignedInteger('approval_revision_number')->default(1);
            $table->string('snapshot_type', 64)->index();
            $table->foreignId('captured_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_summary')->nullable();
            $table->json('payload');
            $table->string('payload_hash', 64)->index();
            $table->json('diff_from_previous')->nullable();
            $table->boolean('is_publishable')->default(false)->index();
            $table->json('governance_issues')->nullable();
            $table->timestamp('captured_at')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();

            $table->index(['approvable_type', 'approvable_id', 'version_number'], 'snapshots_approvable_version_idx');
            $table->index(['content_approval_id', 'snapshot_type'], 'snapshots_approval_type_idx');
        });

        Schema::table('content_approvals', function (Blueprint $table): void {
            $table->text('change_summary')->nullable()->after('rejection_reason');
            $table->text('archive_reason')->nullable()->after('change_summary');
            $table->timestamp('valid_until')->nullable()->after('archive_reason')->index();
            $table->timestamp('unpublished_at')->nullable()->after('archived_at')->index();
            $table->foreignId('rollback_snapshot_id')->nullable()->after('unpublished_at')->constrained('content_snapshots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('content_approvals', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rollback_snapshot_id');
            $table->dropColumn([
                'change_summary',
                'archive_reason',
                'valid_until',
                'unpublished_at',
            ]);
        });

        Schema::dropIfExists('content_snapshots');
    }
};
