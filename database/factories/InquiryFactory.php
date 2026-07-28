<?php

namespace Database\Factories;

use App\Enums\ContactPreference;
use App\Enums\InquiryCategory;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        $contactPreference = fake()->randomElement(ContactPreference::cases());

        return [
            'reference_code' => 'INQ-'.now()->format('Y').'-'.Str::upper(Str::random(6)),
            'citizen_name' => fake()->name(),
            'contact_preference' => $contactPreference,
            'citizen_email' => $contactPreference === ContactPreference::Email ? fake()->safeEmail() : null,
            'citizen_mobile' => $contactPreference === ContactPreference::Sms ? '+639'.fake()->numerify('#########') : null,
            'inquiry_category' => fake()->randomElement(InquiryCategory::cases()),
            'office_unit' => 'Constituent Services Desk',
            'message_body' => fake()->paragraph(),
            'status' => InquiryStatus::Pending,
            'priority' => InquiryPriority::Normal,
            'privacy_consent_at' => now(),
            'first_response_due_at' => now()->addHours((int) config('belzamora.inquiries.unanswered_sla_hours', 48)),
            'retention_expires_at' => now()->addDays(180),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'retention_expires_at' => now()->subDay(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => now()->subHours((int) config('belzamora.inquiries.unanswered_sla_hours', 48) + 4),
            'first_response_due_at' => now()->subHours(4),
            'responded_at' => null,
            'first_responded_at' => null,
            'resolved_at' => null,
            'status' => InquiryStatus::Pending,
        ]);
    }
}
