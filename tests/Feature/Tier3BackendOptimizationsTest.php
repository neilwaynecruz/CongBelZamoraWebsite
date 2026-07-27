<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Events\ContentPublished;
use App\Events\InquirySubmitted;
use App\Jobs\FlagStaleContent;
use App\Models\ContentApproval;
use App\Models\Program;
use App\Models\PublicPage;
use App\Models\User;
use App\Services\AdminContentSearchService;
use App\Services\ContentPublicationService;
use App\Services\PublicCacheInvalidator;
use App\Services\SubmitInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Tier3BackendOptimizationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_cache_is_invalidated_on_publish(): void
    {
        Cache::flush();

        $invalidator = app(PublicCacheInvalidator::class);
        $page = PublicPage::factory()->create([
            'slug' => 'privacy',
            'published_at' => null,
        ]);

        Cache::put($invalidator->staticPageKey('privacy'), ['cached' => true], 60);
        Cache::put(PublicCacheInvalidator::ADMIN_DASHBOARD_COUNTS, ['open_programs' => 1], 30);

        $approval = ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Approved,
        ]);

        app(ContentPublicationService::class)->publish($approval);

        $this->assertFalse(Cache::has($invalidator->staticPageKey('privacy')));
        $this->assertFalse(Cache::has(PublicCacheInvalidator::ADMIN_DASHBOARD_COUNTS));
        $this->assertNotNull($page->fresh()?->published_at);
    }

    public function test_scheduled_publish_uses_publication_service_and_dispatches_event(): void
    {
        Event::fake([ContentPublished::class]);

        $page = PublicPage::factory()->create(['published_at' => null]);
        ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Scheduled,
            'scheduled_for' => now()->subMinute(),
            'approved_at' => now()->subHour(),
        ]);

        $this->artisan('content:publish-scheduled')->assertSuccessful();

        Event::assertDispatched(ContentPublished::class);
        $this->assertNotNull($page->fresh()?->published_at);
        $this->assertSame(ContentApprovalStatus::Published, ContentApproval::query()->first()?->status);
    }

    public function test_inquiry_submission_dispatches_domain_event(): void
    {
        Event::fake([InquirySubmitted::class]);

        $request = Request::create('/contact/inquiries', 'POST', [
            'citizen_name' => 'Test Citizen',
            'contact_preference' => 'email',
            'citizen_email' => 'citizen@example.com',
            'inquiry_category' => 'general_inquiry',
            'message_body' => 'Hello District Office',
            'privacy_consent' => true,
        ]);

        app(SubmitInquiry::class)->handle([
            'citizen_name' => 'Test Citizen',
            'contact_preference' => 'email',
            'citizen_email' => 'citizen@example.com',
            'inquiry_category' => 'general_inquiry',
            'message_body' => 'Hello District Office',
        ], $request);

        Event::assertDispatched(InquirySubmitted::class);
    }

    public function test_civic_content_can_be_soft_deleted_and_restored_by_super_admin(): void
    {
        $super = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $program = Program::factory()->create(['title' => 'Soft Delete Program']);

        $this->assertTrue($super->can('delete', $program));
        $this->assertTrue($super->can('restore', $program));

        $program->delete();
        $this->assertSoftDeleted('programs', ['id' => $program->id]);
        $this->assertDatabaseHas('audit_logs', ['event' => 'deleted']);

        $program->restore();
        $this->assertNull($program->fresh()?->deleted_at);
        $this->assertDatabaseHas('audit_logs', ['event' => 'restored']);
    }

    public function test_admin_content_search_finds_programs(): void
    {
        Program::factory()->create([
            'title' => 'Unique Searchable Scholarship Program',
            'summary' => 'District education support',
            'published_at' => now(),
        ]);

        $results = app(AdminContentSearchService::class)->search('Scholarship');

        $this->assertTrue($results->contains(fn (array $row): bool => str_contains($row['title'], 'Scholarship')));
    }

    public function test_governance_indexes_migration_adds_soft_deletes_column(): void
    {
        $this->assertTrue(Schema::hasColumn('programs', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('source_records', 'deleted_at'));
        $this->assertTrue(Schema::hasColumn('public_pages', 'deleted_at'));
    }

    public function test_flag_stale_content_job_has_retry_policy(): void
    {
        $job = new FlagStaleContent;

        $this->assertSame(3, $job->tries);
        $this->assertSame(60, $job->backoff);
        $this->assertSame(120, $job->timeout);
    }

    public function test_failed_jobs_check_command_passes_when_empty(): void
    {
        $this->artisan('queue:check-failed --threshold=1')->assertSuccessful();
    }

    public function test_soft_deleted_page_is_hidden_from_public_published_scope(): void
    {
        $page = PublicPage::factory()->create([
            'slug' => 'about',
            'published_at' => now()->subDay(),
        ]);

        $page->delete();

        $this->assertNull(PublicPage::query()->published()->where('slug', 'about')->first());
    }
}
