<?php

namespace Database\Factories;

use App\Models\PublicPage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PublicPage>
 */
class PublicPageFactory extends Factory
{
    protected $model = PublicPage::class;

    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'slug' => Str::slug($title),
            'title' => $title,
            'component' => 'Public/PageShell',
            'props' => [
                'breadcrumbs' => [
                    ['title' => 'Home', 'href' => '/'],
                    ['title' => $title],
                ],
                'sections' => [
                    ['title' => 'Verified section', 'body' => fake()->paragraph()],
                ],
                'verifiedAt' => now()->format('F j, Y'),
            ],
            'owner_label' => 'Content Administrator',
            'source_label' => 'Approved content register',
            'source_url' => fake()->url(),
            'verified_at' => now(),
            'review_due_at' => now()->addMonth(),
            'content_owner_id' => User::factory(),
            'approved_by_id' => User::factory(),
            'published_at' => now(),
            'metadata' => [],
        ];
    }
}
