<?php

namespace App\Console\Commands;

use App\Models\PublicPage;
use App\Support\PublicPageProps;
use Illuminate\Console\Command;

class CheckRequiredPublicPages extends Command
{
    protected $signature = 'public-pages:check-required';

    protected $description = 'Verify that required backend-owned public pages are published.';

    public function handle(): int
    {
        $publishedSlugs = PublicPage::query()
            ->published()
            ->whereIn('slug', PublicPageProps::requiredSlugs())
            ->pluck('slug')
            ->all();

        $missing = array_values(array_diff(PublicPageProps::requiredSlugs(), $publishedSlugs));

        if ($missing !== []) {
            $this->error('Missing required published public pages: '.implode(', ', $missing));

            return self::FAILURE;
        }

        $this->info('All required backend-owned public pages are published.');

        return self::SUCCESS;
    }
}
