<?php

namespace Database\Factories;

use App\Enums\ContactPreference;
use App\Enums\InquiryCategory;
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
            'message_body' => fake()->paragraph(),
            'status' => InquiryStatus::Pending,
            'privacy_consent_at' => now(),
            'retention_expires_at' => now()->addDays(180),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'retention_expires_at' => now()->subDay(),
        ]);
    }
}
