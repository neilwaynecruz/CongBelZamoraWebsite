<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectEvidence>
 */
class ProjectEvidenceFactory extends Factory
{
    protected $model = ProjectEvidence::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(5),
            'source_label' => 'Official source',
            'source_url' => fake()->url(),
            'caption' => fake()->sentence(),
            'rights_note' => fake()->sentence(),
            'media_path' => null,
            'verified_at' => now(),
            'verified_by_id' => User::factory(),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }
}
