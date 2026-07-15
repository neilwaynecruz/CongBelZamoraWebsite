<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(5);

        return [
            'project_category_id' => ProjectCategory::factory(),
            'project_status_id' => ProjectStatus::factory(),
            'primary_partner_id' => ProjectPartner::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'barangay' => 'Citywide',
            'location' => fake()->city(),
            'responsible_office' => fake()->company(),
            'partner_label' => fake()->company(),
            'summary' => fake()->paragraph(),
            'evidence_summary' => fake()->paragraph(),
            'impact_note' => fake()->paragraph(),
            'image_guidance' => fake()->sentence(),
            'source_label' => 'Official source',
            'source_url' => fake()->url(),
            'verified_at' => now(),
            'content_owner_id' => User::factory(),
            'approved_by_id' => User::factory(),
            'published_at' => now(),
            'review_due_at' => now()->addMonth(),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }
}
