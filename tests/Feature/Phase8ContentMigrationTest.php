<?php

namespace Tests\Feature;

use App\Enums\ContentApprovalStatus;
use App\Enums\NewsStatusValue;
use App\Enums\ProgramStatus;
use App\Models\ContentApproval;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use App\Models\SourceRecord;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class Phase8ContentMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_staging_seed_content_reconciles_with_approved_mvp_inventory(): void
    {
        $this->seed(DatabaseSeeder::class);

        expect(PublicPage::query()->count())->toBe(6)
            ->and(Program::query()->count())->toBe(4)
            ->and(ServiceGuide::query()->count())->toBe(4)
            ->and(NewsRecord::query()->count())->toBe(5)
            ->and(Project::query()->count())->toBe(5)
            ->and(SourceRecord::query()->count())->toBeGreaterThanOrEqual(23)
            ->and(ContentApproval::query()->where('status', ContentApprovalStatus::Published)->count())->toBeGreaterThanOrEqual(23);
    }

    public function test_migrated_content_has_source_ownership_and_review_dates(): void
    {
        $this->seed(DatabaseSeeder::class);

        $sourceRecords = SourceRecord::query()->get();

        expect($sourceRecords)->not->toBeEmpty();

        foreach ($sourceRecords as $sourceRecord) {
            expect($sourceRecord->content_owner_id)->not->toBeNull()
                ->and($sourceRecord->verified_by_id)->not->toBeNull()
                ->and($sourceRecord->verified_at)->not->toBeNull()
                ->and($sourceRecord->last_reviewed_at)->not->toBeNull()
                ->and($sourceRecord->next_review_at)->not->toBeNull()
                ->and($sourceRecord->public_status)->toBe('published');
        }
    }

    public function test_historical_program_and_news_content_is_not_presented_as_unqualified_current_information(): void
    {
        $this->seed(DatabaseSeeder::class);

        $tulongDunong = Program::query()->where('slug', 'ched-unifast-tulong-dunong')->firstOrFail();
        $staleNews = NewsRecord::query()->where('slug', 'balik-sigla-bigay-saya-review')->firstOrFail();

        expect($tulongDunong->status)->toBe(ProgramStatus::Open)
            ->and($tulongDunong->benefit)->toContain('Historical reference')
            ->and($tulongDunong->application_period)->toContain('reconfirmed')
            ->and($staleNews->status->value)->toBe(NewsStatusValue::StaleReview)
            ->and($staleNews->stale_note)->toContain('Do not display as a current assistance schedule');
    }

    public function test_media_register_requires_rights_alt_text_and_local_upload_before_launch(): void
    {
        $this->seed(DatabaseSeeder::class);

        $mediaAssets = NewsMediaAsset::query()->get();

        expect($mediaAssets)->toHaveCount(5);

        foreach ($mediaAssets as $asset) {
            expect($asset->caption)->not->toBeEmpty()
                ->and($asset->alt_text)->not->toBeEmpty()
                ->and($asset->rights_note)->not->toBeEmpty()
                ->and($asset->verified_at)->not->toBeNull()
                ->and($asset->media_path)->toBeNull();
        }
    }

    public function test_approved_legacy_urls_redirect_to_live_ssr_destinations(): void
    {
        $this->seed(DatabaseSeeder::class);

        $redirects = [
            '/contact-page' => '/contact',
            '/about-bel' => '/about',
            '/biography-full' => '/about/biography',
            '/programs-services' => '/programs',
            '/tulong-dunong-slots' => '/programs/education/tulong-dunong',
        ];

        foreach ($redirects as $oldUrl => $newUrl) {
            $this->get($oldUrl)
                ->assertMovedPermanently()
                ->assertRedirect($newUrl);

            $this->get($newUrl)->assertOk();
        }
    }

    public function test_search_index_files_reference_mvp_public_routes(): void
    {
        $robots = File::get(public_path('robots.txt'));
        $sitemap = File::get(public_path('sitemap.xml'));

        expect($robots)->toContain('Sitemap: https://belzamora.com/sitemap.xml')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/about</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/programs</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/programs/gabay</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/programs/education/tulong-dunong</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/legislation</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/projects</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/news</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/contact</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/privacy</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/accessibility</loc>')
            ->and($sitemap)->toContain('<loc>https://belzamora.com/disclaimer</loc>');
    }
}
