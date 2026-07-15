<?php

namespace Database\Factories;

use App\Enums\ProjectStatusValue;
use App\Models\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectStatus>
 */
class ProjectStatusFactory extends Factory
{
    protected $model = ProjectStatus::class;

    public function definition(): array
    {
        $status = fake()->randomElement(ProjectStatusValue::cases());

        return [
            'name' => $status->label(),
            'slug' => Str::slug($status->label()).'-'.fake()->unique()->numberBetween(100, 999),
            'value' => $status,
            'label' => $status->label(),
            'tone' => $status->tone(),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
