<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Enums\SourceType;
use App\Filament\Resources\PublicPageResource as FilamentPublicPageResource;
use App\Models\ContentApproval;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsolePublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_console_is_policy_protected_and_navigation_is_role_aware(): void
    {
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        PublicPage::factory()->create(['title' => 'Policy Protected Page']);

        $this->actingAs($contentAdmin)
            ->get(route('admin-console.public-pages.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PublicPages/Index')
                ->where('adminConsole.user.role', AdminRole::ContentAdministrator->value)
                ->where('can.create', true)
                ->has('publicPages.data', 1));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($contentAdmin))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Public Pages'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.public-pages.index'))
            ->assertForbidden();
    }

    public function test_public_page_editor_create_and_edit_routes_are_policy_protected(): void
    {
        $editor = User::factory()->adminRole(AdminRole::CommunicationsEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $page = PublicPage::factory()->create(['title' => 'Editable Static Page']);

        $this->actingAs($editor)
            ->get(route('admin-console.public-pages.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PublicPages/Edit')
                ->where('form.mode', 'create')
                ->where('form.options.pageTypes.0.label', 'Landing page'));

        $this->actingAs($editor)
            ->get(route('admin-console.public-pages.edit', $page))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PublicPages/Edit')
                ->where('form.mode', 'edit')
                ->where('form.values.title', 'Editable Static Page'));

        $this->actingAs($reviewer)
            ->get(route('admin-console.public-pages.create'))
            ->assertForbidden();

        $this->actingAs($reviewer)
            ->get(route('admin-console.public-pages.edit', $page))
            ->assertForbidden();
    }

    public function test_public_page_editor_validates_required_json_source_and_governance_fields(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.public-pages.store'), [
                'title' => '',
                'slug' => 'Not Valid',
                'component' => 'Admin/Unsafe',
                'props_json' => '{not-valid',
                'approval' => ['submit_for_review' => true],
            ])
            ->assertSessionHasErrors([
                'title',
                'slug',
                'component',
                'route_name',
                'owner_label',
                'source_label',
                'verified_at',
                'review_due_at',
                'props_json',
                'source_record.title',
                'approval.change_summary',
            ]);
    }

    public function test_public_page_editor_persists_page_source_metadata_and_audit_log(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.public-pages.store'), $this->validPublicPagePayload())
            ->assertRedirect();

        $page = PublicPage::query()->where('slug', 'citizen-charter')->firstOrFail();

        $this->assertSame($staff->id, $page->content_owner_id);
        $this->assertSame('public.citizen-charter', data_get($page->metadata, 'route_name'));
        $this->assertSame('Policy SEO title', data_get($page->metadata, 'seo.title'));
        $this->assertSame('Citizen Charter', data_get($page->props, 'breadcrumbs.1.title'));
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $page->getMorphClass(),
            'sourceable_id' => $page->id,
            'title' => 'Approved content register',
            'source_type' => SourceType::PrimaryOfficial->value,
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $page->getMorphClass(),
            'auditable_id' => $page->id,
            'event' => 'public_page.created',
        ]);
    }

    public function test_public_page_editor_updates_existing_page_and_source_record(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $page = PublicPage::factory()->create([
            'title' => 'Old Public Page',
            'slug' => 'old-page',
            'content_owner_id' => $staff->id,
        ]);
        SourceRecord::factory()->for($page, 'sourceable')->create([
            'title' => 'Old source',
        ]);

        $this->actingAs($staff)
            ->put(route('admin-console.public-pages.update', $page), $this->validPublicPagePayload([
                'title' => 'Updated Public Page',
                'slug' => 'updated-public-page',
                'route_name' => 'public.updated-page',
                'source_record' => [
                    ...$this->validPublicPagePayload()['source_record'],
                    'title' => 'Updated source register',
                ],
            ]))
            ->assertRedirect(route('admin-console.public-pages.show', $page));

        $page->refresh();

        $this->assertSame('Updated Public Page', $page->title);
        $this->assertSame('updated-public-page', $page->slug);
        $this->assertSame('public.updated-page', data_get($page->metadata, 'route_name'));
        $this->assertSame(1, $page->sourceRecords()->count());
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $page->getMorphClass(),
            'sourceable_id' => $page->id,
            'title' => 'Updated source register',
        ]);
    }

    public function test_public_page_editor_can_submit_saved_page_to_approval_workflow(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();

        $payload = $this->validPublicPagePayload([
            'approval' => [
                'submit_for_review' => true,
                'change_summary' => 'Updated page props, SEO metadata, and verified source register fields.',
                'is_fast_track' => false,
                'valid_until' => now()->addMonth()->toDateString(),
            ],
        ]);

        $this->actingAs($staff)
            ->post(route('admin-console.public-pages.store'), $payload)
            ->assertRedirect();

        $page = PublicPage::query()->where('slug', 'citizen-charter')->firstOrFail();
        $approval = ContentApproval::query()
            ->where('approvable_type', $page->getMorphClass())
            ->where('approvable_id', $page->id)
            ->firstOrFail();

        $this->assertSame(ContentApprovalStatus::ForReview, $approval->status);
        $this->assertSame($staff->id, $approval->submitted_by_id);
        $this->assertDatabaseHas('content_snapshots', [
            'content_approval_id' => $approval->id,
            'snapshot_type' => ContentSnapshotType::Submitted->value,
            'captured_by_id' => $staff->id,
        ]);
    }

    public function test_public_pages_console_filters_by_search_status_page_type_and_review(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        PublicPage::factory()->create([
            'title' => 'Citizen Charter',
            'slug' => 'citizen-charter',
            'published_at' => now(),
            'review_due_at' => now()->addDays(2),
            'metadata' => [
                'route_name' => 'public.citizen-charter',
                'page_type' => 'policy',
                'seo' => ['title' => 'Citizen Charter', 'description' => 'Policy page'],
            ],
        ]);
        PublicPage::factory()->create([
            'title' => 'Media Kit',
            'slug' => 'media-kit',
            'published_at' => null,
            'metadata' => ['page_type' => 'utility'],
        ]);

        $this->actingAs($staff)
            ->get(route('admin-console.public-pages.index', [
                'search' => 'Citizen',
                'status' => 'published',
                'page_type' => 'policy',
                'review' => 'due',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/PublicPages/Index')
                ->where('publicPages.data.0.title', 'Citizen Charter')
                ->where('publicPages.data.0.pageType', 'policy')
                ->where('publicPages.meta.total', 1)
                ->where('filters.search', 'Citizen'));
    }

    public function test_public_page_dossier_exposes_source_approval_lifecycle_and_fallback_status(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $page = PublicPage::factory()->create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy',
            'content_owner_id' => $staff->id,
            'approved_by_id' => $reviewer->id,
            'review_due_at' => now()->addDays(20),
            'metadata' => [
                'route_name' => 'public.privacy',
                'page_type' => 'policy',
                'seo' => ['title' => 'Privacy Policy', 'description' => 'Privacy notice.'],
            ],
        ]);
        SourceRecord::factory()
            ->for($page, 'sourceable')
            ->for($staff, 'contentOwner')
            ->for($reviewer, 'verifier')
            ->create([
                'title' => 'Approved privacy source',
                'url' => 'https://example.test/privacy-source',
            ]);
        $approval = ContentApproval::factory()
            ->for($page, 'approvable')
            ->for($staff, 'submitter')
            ->for($reviewer, 'reviewer')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Verified privacy page props and source links.',
                'revision_number' => 2,
                'submitted_at' => now(),
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.public-pages.show', $page))
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->component('Admin/PublicPages/Show')
                ->where('publicPage.title', 'Privacy Policy')
                ->where('publicPage.routeName', 'public.privacy')
                ->where('publicPage.source.recordTitle', 'Approved privacy source')
                ->where('publicPage.governance.contentOwner', $staff->name)
                ->where('publicPage.governance.approval.id', $approval->id)
                ->where('publicPage.governance.approval.href', route('admin-console.content-approvals.show', $approval))
                ->where('publicPage.governance.readiness.passed', true)
                ->where('publicPage.can.update', true)
                ->where('filamentFallbackUrl', null));

        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.public-pages.show', $page))
            ->assertOk()
            ->assertInertia(fn (Assert $inertia) => $inertia
                ->where('filamentFallbackUrl', FilamentPublicPageResource::getUrl('edit', ['record' => $page])));
    }

    public function test_public_page_lifecycle_and_command_search_use_custom_routes(): void
    {
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $superAdmin = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();
        $page = PublicPage::factory()->create(['title' => 'Searchable Public Page']);

        $this->actingAs($contentAdmin)
            ->getJson(route('admin-console.search', ['term' => 'Searchable Public']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Public Page',
                'title' => 'Searchable Public Page',
                'href' => route('admin-console.public-pages.show', $page),
                'usesLegacyFallback' => false,
            ]);

        $this->actingAs($contentAdmin)
            ->post(route('admin-console.public-pages.archive', $page), [
                'reason' => 'Archived after duplicate content review.',
            ])
            ->assertRedirect();

        $this->assertSoftDeleted('public_pages', ['id' => $page->id]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $page->getMorphClass(),
            'auditable_id' => $page->id,
            'event' => 'public.page.archived',
        ]);

        $this->actingAs($superAdmin)
            ->post(route('admin-console.public-pages.restore', $page), [
                'reason' => 'Restored after reviewer confirmed current source.',
            ])
            ->assertRedirect();

        $page->refresh();
        $this->assertFalse($page->trashed());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPublicPagePayload(array $overrides = []): array
    {
        $payload = [
            'title' => 'Citizen Charter',
            'slug' => 'citizen-charter',
            'component' => 'Public/PageShell',
            'route_name' => 'public.citizen-charter',
            'page_type' => 'policy',
            'owner_label' => 'Content Administrator',
            'source_label' => 'Approved content register',
            'source_url' => 'https://example.test/content-register',
            'verified_at' => now()->subDay()->toDateString(),
            'review_due_at' => now()->addMonth()->toDateString(),
            'published_at' => now()->format('Y-m-d\TH:i'),
            'seo_title' => 'Policy SEO title',
            'seo_description' => 'Plain-language SEO description for the static policy page.',
            'canonical_url' => 'https://belzamora.test/citizen-charter',
            'og_image' => '/assets/images/brand/og-card.jpg',
            'props_json' => json_encode([
                'breadcrumbs' => [
                    ['title' => 'Home', 'href' => '/'],
                    ['title' => 'Citizen Charter'],
                ],
                'sections' => [
                    ['title' => 'Verified policy', 'body' => 'Plain-language policy content approved for staging.'],
                ],
                'verifiedAt' => now()->subDay()->format('F j, Y'),
            ], JSON_THROW_ON_ERROR),
            'metadata_json' => json_encode([
                'editor_note' => 'MVP staging content.',
            ], JSON_THROW_ON_ERROR),
            'source_record' => [
                'title' => 'Approved content register',
                'url' => 'https://example.test/content-register',
                'source_type' => SourceType::PrimaryOfficial->value,
                'source_tier' => 'tier_1',
                'public_status' => 'published',
                'description' => 'Static page source reviewed by the content administrator.',
                'verified_at' => now()->subDay()->toDateString(),
                'next_review_at' => now()->addMonth()->toDateString(),
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
