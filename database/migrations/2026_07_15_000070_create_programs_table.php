<?php

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('program_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('href');
            $table->string('sector')->index();
            $table->string('status', 32)->default(ProgramStatus::ForAnnouncement->value)->index();
            $table->string('summary', 500);
            $table->text('description')->nullable();
            $table->string('responsible_office_type', 40)->default(ResponsibleOfficeType::DistrictOffice->value);
            $table->string('responsible_office_label');
            $table->string('partner_agency')->nullable();
            $table->string('beneficiary_group')->nullable();
            $table->string('application_period')->nullable();
            $table->string('benefit')->nullable();
            $table->string('source_label')->nullable();
            $table->string('source_url')->nullable();
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
            $table->index(['sector', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
