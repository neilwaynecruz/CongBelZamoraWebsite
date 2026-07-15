<?php

use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('value', 40)->default(ProjectStatusValue::SourcePending->value)->index();
            $table->string('label');
            $table->string('tone', 40)->default(ProjectStatusTone::SourcePending->value)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_statuses');
    }
};
