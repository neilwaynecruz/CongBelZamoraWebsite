<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('source_records', function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('sourceable');
            $table->string('title', 180);
            $table->string('url', 500)->nullable();
            $table->string('source_type', 64)->index();
            $table->string('source_tier', 32)->default('tier_1')->index();
            $table->text('description')->nullable();
            $table->foreignId('content_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamp('next_review_at')->nullable()->index();
            $table->timestamp('effective_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('public_status', 64)->nullable()->index();
            $table->text('internal_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('source_records');
    }
};
