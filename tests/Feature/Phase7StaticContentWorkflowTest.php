<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\PublicPageResource;
use App\Jobs\FlagStaleContent;
use App\Models\AuditLog;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use App\Models\User;
use App\Notifications\StaleContentDigestNotification;
use Database\Seeders\PublicPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase7StaticContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_schema_exists(): void
    {
        expect(Schema::hasTable('public_pages'))->toBeTrue()
            ->and(Schema::hasColumn('public_pages', 'props'))->toBeTrue()
            ->and(Schema::hasColumn('public_pages', 'source_label'))->toBeTrue()
            ->and(Schema::hasColumn('public_pages', 'verified_at'))->toBeTrue()
            ->and(Schema::hasColumn('public_pages', 'review_due_at'))->toBeTrue()
            ->and(Schema::hasColumn('public_pages', 'content_owner_id'))->toBeTrue()
            ->and(Schema::hasColumn('public_pages', 'approved_by_id'))->toBeTrue();
    }

    public function test_seeded_public_pages_have_source_and_approval_records(): void
    {
        $this->seed(PublicPageSeeder::class);

        $about = PublicPage::query()->where('slug', 'about')->firstOrFail();

        expect(PublicPage::query()->count())->toBe(6)
            ->and($about->props['profile']['publicName'])->toBe('Atty. Bel Zamora')
            ->and($about->sourceRecords)->toHaveCount(1)
            ->and($about->approvalRecords)->toHaveCount(1)
            ->and(SourceRecord::query()->where('sourceable_type', PublicPage::class)->count())->toBe(6);
    }

    public function test_public_page_and_audit_policies_are_least_privilege(): void
    {
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $communicationsEditor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $page = PublicPage::factory()->create();
        $auditLog = AuditLog::factory()->for($contentAdmin, 'actor')->create();

        expect($contentAdmin->can('update', $page))->toBeTrue()
            ->and($communicationsEditor->can('update', $page))->toBeTrue()
            ->and($reviewer->can('approve', $page))->toBeTrue()
            ->and($reviewer->can('update', $page))->toBeFalse()
            ->and($auditor->can('view', $page))->toBeTrue()
            ->and($auditor->can('update', $page))->toBeFalse()
            ->and($inquiryOfficer->can('view', $page))->toBeFalse()
            ->and($auditor->can('view', $auditLog))->toBeTrue()
            ->and($contentAdmin->can('view', $auditLog))->toBeFalse();
    }

    public function test_filament_public_page_and_audit_resources_are_role_gated(): void
    {
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $this->actingAs($contentAdmin)
            ->get(PublicPageResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($auditor)
            ->get(AuditLogResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($inquiryOfficer)
            ->get(PublicPageResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($contentAdmin)
            ->get(AuditLogResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_static_public_routes_use_backend_pages_with_phase_6_contracts(): void
    {
        $this->seed(PublicPageSeeder::class);

        $this->get(route('public.about'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/About')
                ->where('profile.publicName', 'Atty. Bel Zamora')
                ->has('facts', 6)
                ->where('sourceLinks.1.href', 'https://www.congress.gov.ph/house-members/view/memberships/?member=K099')
                ->where('verifiedAt', 'July 12, 2026')
                ->where('contentReview.owner', 'Content Administrator with Chief of Staff review'));

        $this->get(route('public.contact'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Contact')
                ->where('office.name', 'District Office of Atty. Congresswoman Bel Zamora')
                ->has('inquiryCategories', 4)
                ->where('inquiryAction', route('public.contact.inquiries.store'))
                ->where('confirmation', null));
    }

    public function test_stale_content_job_logs_audit_events_and_notifies_review_roles(): void
    {
        Notification::fake();

        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $page = PublicPage::factory()->create(['review_due_at' => now()->subDay()]);
        SourceRecord::factory()
            ->for($page, 'sourceable')
            ->create(['next_review_at' => now()->subDay()]);

        FlagStaleContent::dispatchSync();

        expect(AuditLog::query()->where('event', 'content.review_overdue')->count())->toBe(1)
            ->and(AuditLog::query()->where('event', 'source_record.review_overdue')->count())->toBe(1);

        Notification::assertSentTo($reviewer, StaleContentDigestNotification::class);
        Notification::assertNotSentTo($inquiryOfficer, StaleContentDigestNotification::class);
    }

    public function test_stale_content_command_is_registered_and_dispatches_job(): void
    {
        Artisan::call('content:flag-stale');

        expect(Artisan::output())->toContain('Queued public-content freshness check.');
    }
}
