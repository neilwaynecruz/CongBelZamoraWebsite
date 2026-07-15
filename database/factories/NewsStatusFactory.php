<?php

namespace Database\Factories;

use App\Enums\NewsStatusValue;
use App\Models\NewsStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsStatus>
 */
class NewsStatusFactory extends Factory
{
    protected $model = NewsStatus::class;

    public function definition(): array
    {
        $status = fake()->randomElement(NewsStatusValue::cases());

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
