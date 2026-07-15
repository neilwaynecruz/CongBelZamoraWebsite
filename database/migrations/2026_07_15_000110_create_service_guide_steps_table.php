<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_guide_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_guide_id')->constrained()->cascadeOnDelete();
            $table->string('section', 32)->index();
            $table->string('item_text', 500);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['service_guide_id', 'section', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_guide_steps');
    }
};
