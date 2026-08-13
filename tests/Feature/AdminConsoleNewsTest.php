<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Enums\NewsStatusTone;
use App\Enums\NewsStatusValue;
use App\Enums\SourceType;
use App\Filament\Resources\NewsRecordResource as FilamentNewsRecordResource;
use App\Models\ContentApproval;
use App\Models\NewsCategory;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\NewsStatus;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleNewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_console_is_policy_protected_and_navigation_is_role_aware(): void
    {
        $communicationsEditor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        NewsRecord::factory()->create(['title' => 'Policy Protected Update']);

        $this->actingAs($communicationsEditor)
            ->get(route('admin-console.news.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/News/Index')
                ->where('adminConsole.user.role', AdminRole::CommunicationsEditor->value)
                ->where('can.create', true)
                ->has('adminConsole.navigation'));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($communicationsEditor))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('News & Media'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.news.index'))
            ->assertForbidden();
    }

    public function test_news_editor_create_and_edit_routes_are_policy_protected(): void
    {
        $editor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        [$category, $status] = $this->newsTaxonomy();
        $record = NewsRecord::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->create(['title' => 'Editable Update']);

        $this->actingAs($editor)
            ->get(route('admin-console.news.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/News/Edit')
                ->where('form.mode', 'create')
                ->where('form.options.categories.0.label', 'Official Updates')
                ->where('form.options.statuses.0.label', 'Current advisory'));

        $this->actingAs($editor)
            ->get(route('admin-console.news.edit', $record))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/News/Edit')
                ->where('form.mode', 'edit')
                ->where('form.values.title', 'Editable Update'));

        $this->actingAs($reviewer)
            ->get(route('admin-console.news.create'))
            ->assertForbidden();

        $this->actingAs($reviewer)
            ->get(route('admin-console.news.edit', $record))
            ->assertForbidden();
    }

    public function test_news_editor_validates_required_source_media_and_governance_fields(): void
    {
        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.news.store'), [
                'title' => '',
                'slug' => 'Not Valid',
                'approval' => ['submit_for_review' => true],
            ])
            ->assertSessionHasErrors([
                'news_category_id',
                'news_status_id',
                'title',
                'slug',
                'published_at',
                'source_url',
                'official_update',
                'source_record.title',
                'approval.change_summary',
            ]);
    }

    public function test_news_editor_persists_news_source_and_media_records(): void
    {
        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        [$category, $status] = $this->newsTaxonomy();

        $this->actingAs($staff)
            ->post(route('admin-console.news.store'), $this->validNewsPayload($category, $status))
            ->assertRedirect();

        $record = NewsRecord::query()->where('slug', 'custom-service-update')->firstOrFail();

        $this->assertSame($staff->id, $record->content_owner_id);
        $this->assertDatabaseHas('news_records', [
            'id' => $record->id,
            'title' => 'Custom Public Service Update',
            'source_url' => 'https://www.sanjuancity.gov.ph/news',
        ]);
        $this->assertDatabaseHas('news_media_assets', [
            'news_record_id' => $record->id,
            'title' => 'Official update image',
            'media_path' => 'news/official-update.jpg',
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $record->getMorphClass(),
            'sourceable_id' => $record->id,
            'title' => 'Official San Juan news page',
            'source_type' => SourceType::PrimaryOfficial->value,
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $record->getMorphClass(),
            'auditable_id' => $record->id,
            'event' => 'news.created',
        ]);
    }

    public function test_news_editor_updates_nested_media_records_transactionally(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        [$category, $status] = $this->newsTaxonomy();
        $record = NewsRecord::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->create([
                'title' => 'Old News Title',
                'slug' => 'old-news-title',
                'content_owner_id' => $staff->id,
            ]);
        $asset = NewsMediaAsset::factory()->for($record)->create(['title' => 'Old media asset']);

        $payload = $this->validNewsPayload($category, $status, [
            'title' => 'Updated News Title',
            'slug' => 'updated-news-title',
            'media_assets' => [
                [
                    'title' => 'Updated media asset',
                    'media_type' => 'image',
                    'media_path' => 'news/updated.jpg',
                    'source_label' => 'Official source',
                    'source_url' => 'https://www.sanjuancity.gov.ph/news',
                    'caption' => 'Updated caption for official reuse.',
                    'alt_text' => 'Staff assisting residents during a public service update.',
                    'rights_note' => 'Rights approved by the communications editor.',
                    'verified_at' => now()->subDay()->toDateString(),
                ],
            ],
        ]);

        $this->actingAs($staff)
            ->put(route('admin-console.news.update', $record), $payload)
            ->assertRedirect(route('admin-console.news.show', $record));

        $record->refresh();

        $this->assertSame('Updated News Title', $record->title);
        $this->assertSame('updated-news-title', $record->slug);
        $this->assertSame(1, $record->mediaAssets()->count());
        $this->assertSoftDeleted('news_media_assets', ['id' => $asset->id]);
        $this->assertDatabaseHas('news_media_assets', [
            'news_record_id' => $record->id,
            'title' => 'Updated media asset',
        ]);
    }

    public function test_news_editor_can_submit_saved_news_to_approval_workflow(): void
    {
        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        [$category, $status] = $this->newsTaxonomy();

        $payload = $this->validNewsPayload($category, $status, [
            'approval' => [
                'submit_for_review' => true,
                'change_summary' => 'Created the news record, verified the official source, and checked media-rights metadata.',
                'is_fast_track' => false,
                'valid_until' => now()->addMonth()->toDateString(),
            ],
        ]);

        $this->actingAs($staff)
            ->post(route('admin-console.news.store'), $payload)
            ->assertRedirect();

        $record = NewsRecord::query()->where('slug', 'custom-service-update')->firstOrFail();
        $approval = ContentApproval::query()
            ->where('approvable_type', $record->getMorphClass())
            ->where('approvable_id', $record->id)
            ->firstOrFail();

        $this->assertSame(ContentApprovalStatus::ForReview, $approval->status);
        $this->assertSame($staff->id, $approval->submitted_by_id);
        $this->assertDatabaseHas('content_snapshots', [
            'content_approval_id' => $approval->id,
            'snapshot_type' => ContentSnapshotType::Submitted->value,
            'captured_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $approval->getMorphClass(),
            'auditable_id' => $approval->id,
            'event' => 'content.submitted_for_review',
        ]);
    }

    public function test_news_console_filters_by_search_status_category_and_year(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        [$updates, $current] = $this->newsTaxonomy();
        $media = NewsCategory::factory()->create(['name' => 'Media Releases']);
        $archived = NewsStatus::factory()->create([
            'value' => NewsStatusValue::Archived,
            'label' => 'Archived official update',
            'tone' => NewsStatusTone::Archived,
        ]);

        NewsRecord::factory()
            ->for($updates, 'category')
            ->for($current, 'status')
            ->create([
                'title' => 'Education Assistance Orientation',
                'published_at' => now()->setYear(2026),
            ]);
        NewsRecord::factory()
            ->for($media, 'category')
            ->for($archived, 'status')
            ->create([
                'title' => 'Archived Media Release',
                'published_at' => now()->setYear(2025),
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.news.index', [
                'search' => 'Education',
                'status' => $current->id,
                'category' => $updates->id,
                'year' => '2026',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/News/Index')
                ->where('newsRecords.data.0.title', 'Education Assistance Orientation')
                ->where('newsRecords.data.0.category.name', 'Official Updates')
                ->where('newsRecords.meta.total', 1)
                ->where('filters.search', 'Education')
                ->where('filters.category', (string) $updates->id));
    }

    public function test_news_dossier_exposes_source_approval_media_and_fallback_status(): void
    {
        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        [$category, $status] = $this->newsTaxonomy();
        $record = NewsRecord::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->create([
                'title' => 'Education Assistance Orientation',
                'source_label' => 'Official San Juan news page',
                'source_url' => 'https://www.sanjuancity.gov.ph/news',
                'content_owner_id' => $staff->id,
                'approved_by_id' => $reviewer->id,
                'review_due_at' => now()->addDays(20),
            ]);
        NewsMediaAsset::factory()->for($record)->for($reviewer, 'verifier')->create([
            'title' => 'Official orientation photo',
            'media_path' => 'news/orientation.jpg',
        ]);
        SourceRecord::factory()
            ->for($record, 'sourceable')
            ->for($staff, 'contentOwner')
            ->for($reviewer, 'verifier')
            ->create([
                'title' => 'Official San Juan news source',
                'url' => 'https://www.sanjuancity.gov.ph/news',
            ]);
        $approval = ContentApproval::factory()
            ->for($record, 'approvable')
            ->for($staff, 'submitter')
            ->for($reviewer, 'reviewer')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Verified source, stale note, and media-rights metadata.',
                'revision_number' => 2,
                'submitted_at' => now(),
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.news.show', $record))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/News/Show')
                ->where('newsRecord.title', 'Education Assistance Orientation')
                ->where('newsRecord.source.recordTitle', 'Official San Juan news source')
                ->where('newsRecord.mediaAssets.0.title', 'Official orientation photo')
                ->where('newsRecord.governance.contentOwner', $staff->name)
                ->where('newsRecord.governance.approval.id', $approval->id)
                ->where('newsRecord.governance.approval.href', route('admin-console.content-approvals.show', $approval))
                ->where('newsRecord.governance.readiness.passed', true)
                ->where('newsRecord.can.update', true)
                ->where('filamentFallbackUrl', null));

        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.news.show', $record))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filamentFallbackUrl', FilamentNewsRecordResource::getUrl('edit', ['record' => $record])));
    }

    public function test_command_palette_search_uses_custom_news_route_after_module_migration(): void
    {
        $staff = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $record = NewsRecord::factory()->create(['title' => 'Custom Searchable News']);

        $this->actingAs($staff)
            ->getJson(route('admin-console.search', ['term' => 'Custom Searchable']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'News',
                'title' => 'Custom Searchable News',
                'href' => route('admin-console.news.show', $record),
                'usesLegacyFallback' => false,
            ]);
    }

    /**
     * @return array{0: NewsCategory, 1: NewsStatus}
     */
    private function newsTaxonomy(): array
    {
        $category = NewsCategory::factory()->create(['name' => 'Official Updates']);
        $status = NewsStatus::factory()->create([
            'value' => NewsStatusValue::Current,
            'label' => 'Current advisory',
            'tone' => NewsStatusTone::Current,
        ]);

        return [$category, $status];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validNewsPayload(
        NewsCategory $category,
        NewsStatus $status,
        array $overrides = [],
    ): array {
        $payload = [
            'news_category_id' => $category->id,
            'news_status_id' => $status->id,
            'title' => 'Custom Public Service Update',
            'slug' => 'custom-service-update',
            'published_at_label' => 'August 10, 2026',
            'published_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'public_updated_at_label' => 'August 10, 2026',
            'public_updated_at' => now()->subDay()->format('Y-m-d\TH:i'),
            'source_label' => 'Official San Juan news page',
            'source_url' => 'https://www.sanjuancity.gov.ph/news',
            'summary' => 'Plain-language summary of the public-service update.',
            'official_update' => 'The District Office reviewed the official update and source before public reuse.',
            'next_step' => 'Residents should confirm current details through the official source before acting.',
            'stale_note' => 'If the review date passes, mark this item for source review before reuse.',
            'owner_label' => 'Communications Desk',
            'source_verified_at' => now()->subDay()->toDateString(),
            'review_due_at' => now()->addMonth()->toDateString(),
            'sort_order' => 2,
            'source_record' => [
                'title' => 'Official San Juan news page',
                'url' => 'https://www.sanjuancity.gov.ph/news',
                'source_type' => SourceType::PrimaryOfficial->value,
                'source_tier' => 'tier_1',
                'public_status' => 'published',
                'description' => 'Official source reviewed by the communications editor.',
                'verified_at' => now()->subDay()->toDateString(),
                'next_review_at' => now()->addMonth()->toDateString(),
            ],
            'media_assets' => [
                [
                    'title' => 'Official update image',
                    'media_type' => 'image',
                    'media_path' => 'news/official-update.jpg',
                    'source_label' => 'Official San Juan news page',
                    'source_url' => 'https://www.sanjuancity.gov.ph/news',
                    'caption' => 'Official image approved for the update.',
                    'alt_text' => 'District Office staff supporting a public-service update.',
                    'rights_note' => 'Approved local derivative with rights cleared by communications.',
                    'verified_at' => now()->subDay()->toDateString(),
                ],
            ],
            'approval' => [
                'submit_for_review' => false,
                'change_summary' => '',
                'is_fast_track' => false,
                'valid_until' => null,
            ],
        ];

        foreach ($overrides as $key => $value) {
            $payload[$key] = $value;
        }

        return $payload;
    }
}
