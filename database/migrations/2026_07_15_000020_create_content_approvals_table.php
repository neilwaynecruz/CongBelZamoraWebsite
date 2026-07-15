<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_approvals', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('approvable');
            $table->string('status', 64)->default('draft')->index();
            $table->unsignedInteger('revision_number')->default(1);
            $table->foreignId('submitted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('archived_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_fast_track')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['approvable_type', 'approvable_id', 'revision_number'], 'content_approvals_revision_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_approvals');
    }
};
