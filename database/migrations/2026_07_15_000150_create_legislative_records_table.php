<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legislative_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('legislative_role_id')->constrained()->restrictOnDelete();
            $table->foreignId('legislative_status_id')->constrained()->restrictOnDelete();
            $table->foreignId('legislative_topic_id')->constrained()->restrictOnDelete();
            $table->string('bill_number')->unique();
            $table->string('slug')->unique();
            $table->string('official_title');
            $table->string('public_title');
            $table->string('congress');
            $table->string('committee');
            $table->string('filed_at_label')->default('Source-pending date');
            $table->date('filed_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('plain_language_summary');
            $table->text('why_it_matters');
            $table->text('san_juan_relevance');
            $table->string('official_source_label');
            $table->string('official_source_url');
            $table->foreignId('content_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('review_due_at')->nullable()->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'sort_order']);
            $table->index(
                ['legislative_role_id', 'legislative_status_id', 'legislative_topic_id'],
                'leg_records_role_status_topic_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legislative_records');
    }
};
