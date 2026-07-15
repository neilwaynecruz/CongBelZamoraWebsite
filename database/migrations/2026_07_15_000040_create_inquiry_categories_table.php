<?php

use App\Enums\InquiryCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiry_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('label', 160);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();

        DB::table('inquiry_categories')->insert(
            collect(InquiryCategory::cases())
                ->values()
                ->map(fn (InquiryCategory $category, int $index): array => [
                    'code' => $category->value,
                    'label' => $category->label(),
                    'description' => $category->description(),
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all()
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_categories');
    }
};
