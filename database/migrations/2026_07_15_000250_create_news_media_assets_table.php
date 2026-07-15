<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_media_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('news_record_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('media_type')->default('image')->index();
            $table->string('media_path')->nullable();
            $table->string('source_label');
            $table->string('source_url');
            $table->text('caption')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('rights_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['news_record_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_media_assets');
    }
};
