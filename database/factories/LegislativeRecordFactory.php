<?php

namespace Database\Factories;

use App\Models\LegislativeRecord;
use App\Models\LegislativeRole;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LegislativeRecord>
 */
class LegislativeRecordFactory extends Factory
{
    protected $model = LegislativeRecord::class;

    public function definition(): array
    {
        $billNumber = 'HB'.fake()->unique()->numberBetween(10000, 99999);
        $publicTitle = fake()->unique()->sentence(5);

        return [
            'legislative_role_id' => LegislativeRole::factory(),
            'legislative_status_id' => LegislativeStatus::factory(),
            'legislative_topic_id' => LegislativeTopic::factory(),
            'bill_number' => $billNumber,
            'slug' => Str::slug($billNumber.' '.$publicTitle),
            'official_title' => fake()->sentence(8),
            'public_title' => $publicTitle,
            'congress' => '20th Congress',
            'committee' => fake()->sentence(4),
            'filed_at_label' => 'Source-pending date',
            'filed_at' => null,
            'verified_at' => now(),
            'plain_language_summary' => fake()->paragraph(),
            'why_it_matters' => fake()->paragraph(),
            'san_juan_relevance' => fake()->paragraph(),
            'official_source_label' => 'View House member record',
            'official_source_url' => 'https://www.congress.gov.ph/',
            'content_owner_id' => User::factory(),
            'approved_by_id' => User::factory(),
            'published_at' => now(),
            'review_due_at' => now()->addMonth(),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => null,
        ]);
    }
}
