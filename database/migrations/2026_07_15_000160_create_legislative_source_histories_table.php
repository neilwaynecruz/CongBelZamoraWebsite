<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legislative_source_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('legislative_record_id')->constrained()->cascadeOnDelete();
            $table->string('source_label');
            $table->string('source_url');
            $table->string('status_snapshot');
            $table->foreignId('checked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['legislative_record_id', 'checked_at'], 'leg_source_hist_record_checked_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legislative_source_histories');
    }
};
