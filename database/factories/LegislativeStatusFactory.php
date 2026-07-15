<?php

namespace Database\Factories;

use App\Enums\LegislativeStatusTone;
use App\Models\LegislativeStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LegislativeStatus>
 */
class LegislativeStatusFactory extends Factory
{
    protected $model = LegislativeStatus::class;

    public function definition(): array
    {
        $label = fake()->unique()->sentence(4);

        return [
            'name' => $label,
            'slug' => Str::slug($label).'-'.fake()->unique()->numberBetween(100, 999),
            'label' => $label,
            'tone' => fake()->randomElement(LegislativeStatusTone::cases()),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
