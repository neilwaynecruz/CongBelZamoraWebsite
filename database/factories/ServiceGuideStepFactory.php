<?php

namespace Database\Factories;

use App\Models\ServiceGuide;
use App\Models\ServiceGuideStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceGuideStep>
 */
class ServiceGuideStepFactory extends Factory
{
    protected $model = ServiceGuideStep::class;

    public function definition(): array
    {
        return [
            'service_guide_id' => ServiceGuide::factory(),
            'section' => fake()->randomElement(['prepare', 'do_not_submit']),
            'item_text' => fake()->sentence(8),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
