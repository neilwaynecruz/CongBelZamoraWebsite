<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ProgramRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramRequirement>
 */
class ProgramRequirementFactory extends Factory
{
    protected $model = ProgramRequirement::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'group_title' => fake()->randomElement(['Identity and residency', 'School and eligibility']),
            'item_text' => fake()->sentence(8),
            'note' => fake()->optional()->sentence(6),
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
