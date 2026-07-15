<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'program_category_id' => ProgramCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'href' => '/programs',
            'sector' => fake()->randomElement(['Education', 'Health', 'Solo Parents', 'Digital Services']),
            'status' => fake()->randomElement(ProgramStatus::cases()),
            'summary' => fake()->sentence(14),
            'description' => fake()->paragraph(),
            'responsible_office_type' => fake()->randomElement(ResponsibleOfficeType::cases()),
            'responsible_office_label' => fake()->company(),
            'partner_agency' => fake()->company(),
            'beneficiary_group' => fake()->sentence(4),
            'application_period' => fake()->sentence(5),
            'benefit' => fake()->sentence(8),
            'source_label' => 'Official source',
            'source_url' => fake()->url(),
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

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => null,
        ]);
    }
}
