<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('news_status_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('published_at_label');
            $table->timestamp('published_at')->nullable()->index();
            $table->string('public_updated_at_label');
            $table->timestamp('public_updated_at')->nullable()->index();
            $table->string('source_label');
            $table->string('source_url');
            $table->text('summary');
            $table->text('official_update');
            $table->text('next_step');
            $table->text('stale_note');
            $table->string('owner_label');
            $table->timestamp('source_verified_at')->nullable();
            $table->timestamp('review_due_at')->nullable()->index();
            $table->foreignId('content_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'sort_order']);
            $table->index(['news_category_id', 'news_status_id'], 'news_records_category_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_records');
    }
};
