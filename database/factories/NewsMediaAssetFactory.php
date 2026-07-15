<?php

namespace Database\Factories;

use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsMediaAsset>
 */
class NewsMediaAssetFactory extends Factory
{
    protected $model = NewsMediaAsset::class;

    public function definition(): array
    {
        return [
            'news_record_id' => NewsRecord::factory(),
            'title' => fake()->sentence(5),
            'media_type' => 'image',
            'media_path' => null,
            'source_label' => 'Official source',
            'source_url' => fake()->url(),
            'caption' => fake()->sentence(),
            'alt_text' => fake()->sentence(),
            'rights_note' => fake()->sentence(),
            'verified_at' => now(),
            'verified_by_id' => User::factory(),
            'sort_order' => fake()->numberBetween(1, 20),
            'metadata' => [],
        ];
    }
}
