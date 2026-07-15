<?php

use App\Enums\InquiryStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_code', 24)->unique();
            $table->text('citizen_name');
            $table->string('contact_preference', 16);
            $table->text('citizen_email')->nullable();
            $table->text('citizen_mobile')->nullable();
            $table->string('inquiry_category', 80)->index();
            $table->text('message_body');
            $table->string('status', 64)->default(InquiryStatus::Pending->value)->index();
            $table->foreignId('assigned_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('privacy_consent_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('retention_expires_at')->index();
            $table->timestamp('anonymized_at')->nullable()->index();
            $table->json('routing_notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
