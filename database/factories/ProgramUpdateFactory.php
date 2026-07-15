<?php

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\Program;
use App\Models\ProgramUpdate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgramUpdate>
 */
class ProgramUpdateFactory extends Factory
{
    protected $model = ProgramUpdate::class;

    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'title' => fake()->sentence(5),
            'summary' => fake()->paragraph(),
            'status' => fake()->randomElement(ProgramStatus::cases()),
            'source_label' => 'Official advisory',
            'source_url' => fake()->url(),
            'verified_at' => now(),
            'content_owner_id' => User::factory(),
            'published_at' => now(),
            'metadata' => [],
        ];
    }
}
