<?php

namespace Database\Factories;

use App\Models\InquiryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InquiryCategory>
 */
class InquiryCategoryFactory extends Factory
{
    protected $model = InquiryCategory::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'label' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
