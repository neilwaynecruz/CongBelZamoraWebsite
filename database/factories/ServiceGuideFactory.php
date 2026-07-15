<?php

namespace Database\Factories;

use App\Enums\ResponsibleOfficeType;
use App\Models\ServiceGuide;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceGuide>
 */
class ServiceGuideFactory extends Factory
{
    protected $model = ServiceGuide::class;

    public function definition(): array
    {
        $label = fake()->unique()->sentence(4);

        return [
            'title' => $label,
            'slug' => Str::slug($label).'-'.fake()->unique()->numberBetween(100, 999),
            'label' => $label,
            'concern' => fake()->sentence(6),
            'responsible_office_type' => fake()->randomElement(ResponsibleOfficeType::cases()),
            'responsible_office_label' => fake()->company(),
            'district_office_role' => fake()->paragraph(),
            'can_district_office_directly_handle' => fake()->boolean(),
            'explanation' => fake()->paragraph(),
            'next_step' => fake()->sentence(12),
            'contact_label' => 'Use official channel',
            'contact_href' => '/contact#inquiry-form',
            'verified_at' => now(),
            'content_owner_id' => User::factory(),
            'approved_by_id' => User::factory(),
            'published_at' => now(),
            'review_due_at' => now()->addMonth(),
            'is_featured' => false,
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }
}
