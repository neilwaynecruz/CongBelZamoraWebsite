<?php

namespace Database\Factories;

use App\Models\NewsCategory;
use App\Models\NewsRecord;
use App\Models\NewsStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<NewsRecord>
 */
class NewsRecordFactory extends Factory
{
    protected $model = NewsRecord::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'news_category_id' => NewsCategory::factory(),
            'news_status_id' => NewsStatus::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 999),
            'published_at_label' => now()->format('F j, Y'),
            'published_at' => now(),
            'public_updated_at_label' => now()->format('F j, Y'),
            'public_updated_at' => now(),
            'source_label' => 'Official source',
            'source_url' => fake()->url(),
            'summary' => fake()->paragraph(),
            'official_update' => fake()->paragraph(),
            'next_step' => fake()->sentence(),
            'stale_note' => fake()->sentence(),
            'owner_label' => 'Communications Lead',
            'source_verified_at' => now(),
            'review_due_at' => now()->addMonth(),
            'content_owner_id' => User::factory(),
            'approved_by_id' => User::factory(),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }
}
