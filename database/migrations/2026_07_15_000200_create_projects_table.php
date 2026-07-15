<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('project_status_id')->constrained()->restrictOnDelete();
            $table->foreignId('primary_partner_id')->nullable()->constrained('project_partners')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('barangay')->index();
            $table->string('location');
            $table->string('responsible_office');
            $table->string('partner_label');
            $table->text('summary');
            $table->text('evidence_summary');
            $table->text('impact_note');
            $table->text('image_guidance');
            $table->string('source_label');
            $table->string('source_url');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('content_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('review_due_at')->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'sort_order']);
            $table->index(['project_category_id', 'project_status_id'], 'projects_category_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
