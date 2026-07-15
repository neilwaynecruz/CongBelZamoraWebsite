<?php

namespace Database\Factories;

use App\Models\LegislativeTopic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LegislativeTopic>
 */
class LegislativeTopicFactory extends Factory
{
    protected $model = LegislativeTopic::class;

    public function definition(): array
    {
        $name = fake()->unique()->word().' '.fake()->unique()->word().' '.fake()->unique()->word();

        return [
            'name' => str($name)->title()->toString(),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
