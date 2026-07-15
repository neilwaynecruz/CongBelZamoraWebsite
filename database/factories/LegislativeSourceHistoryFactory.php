<?php

namespace Database\Factories;

use App\Models\LegislativeRecord;
use App\Models\LegislativeSourceHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegislativeSourceHistory>
 */
class LegislativeSourceHistoryFactory extends Factory
{
    protected $model = LegislativeSourceHistory::class;

    public function definition(): array
    {
        return [
            'legislative_record_id' => LegislativeRecord::factory(),
            'source_label' => 'Official legislative source',
            'source_url' => 'https://www.congress.gov.ph/',
            'status_snapshot' => fake()->sentence(5),
            'checked_by_id' => User::factory(),
            'checked_at' => now(),
            'notes' => fake()->sentence(),
            'metadata' => [],
        ];
    }
}
