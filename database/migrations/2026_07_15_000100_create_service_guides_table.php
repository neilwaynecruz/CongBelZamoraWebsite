<?php

use App\Enums\ResponsibleOfficeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_guides', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('label');
            $table->string('concern');
            $table->string('responsible_office_type', 40)->default(ResponsibleOfficeType::DistrictOffice->value)->index();
            $table->string('responsible_office_label');
            $table->text('district_office_role');
            $table->boolean('can_district_office_directly_handle')->default(false);
            $table->text('explanation');
            $table->text('next_step');
            $table->string('contact_label');
            $table->string('contact_href');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('content_owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('review_due_at')->nullable()->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['published_at', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_guides');
    }
};
