<?php

namespace Database\Factories;

use App\Enums\SourceType;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SourceRecord>
 */
class SourceRecordFactory extends Factory
{
    protected $model = SourceRecord::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'url' => fake()->url(),
            'source_type' => SourceType::PrimaryOfficial,
            'source_tier' => 'tier_1',
            'description' => fake()->sentence(),
            'content_owner_id' => User::factory(),
            'verified_by_id' => User::factory(),
            'verified_at' => now(),
            'last_reviewed_at' => now(),
            'next_review_at' => now()->addMonth(),
            'public_status' => 'published',
            'metadata' => ['factory' => true],
        ];
    }
}
