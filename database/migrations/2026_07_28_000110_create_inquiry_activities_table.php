<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 64)->index();
            $table->string('visibility', 32)->default('internal')->index();
            $table->text('body')->nullable();
            $table->string('old_status', 64)->nullable();
            $table->string('new_status', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['inquiry_id', 'created_at'], 'inquiry_activities_inquiry_created_idx');
            $table->index(['type', 'created_at'], 'inquiry_activities_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_activities');
    }
};
