<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Enums\SourceType;
use App\Filament\Resources\ProjectResource as FilamentProjectResource;
use App\Models\ContentApproval;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectEvidence;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_console_is_policy_protected_and_navigation_is_role_aware(): void
    {
        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        Project::factory()->create(['title' => 'Policy Protected Project']);

        $this->actingAs($projectEditor)
            ->get(route('admin-console.projects.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Index')
                ->where('adminConsole.user.role', AdminRole::ProjectEditor->value)
                ->where('can.create', true)
                ->has('adminConsole.navigation'));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($projectEditor))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Projects'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.projects.index'))
            ->assertForbidden();
    }

    public function test_project_editor_create_and_edit_routes_are_policy_protected(): void
    {
        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $category = ProjectCategory::factory()->create(['name' => 'Digital Government']);
        $status = ProjectStatus::factory()->create(['label' => 'Active / ongoing service']);
        $project = Project::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->create(['title' => 'Editable Project']);

        $this->actingAs($projectEditor)
            ->get(route('admin-console.projects.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Edit')
                ->where('form.mode', 'create')
                ->where('form.options.categories.0.label', 'Digital Government')
                ->where('form.options.statuses.0.label', 'Active / ongoing service'));

        $this->actingAs($projectEditor)
            ->get(route('admin-console.projects.edit', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Edit')
                ->where('form.mode', 'edit')
                ->where('form.values.title', 'Editable Project'));

        $this->actingAs($reviewer)
            ->get(route('admin-console.projects.create'))
            ->assertForbidden();

        $this->actingAs($reviewer)
            ->get(route('admin-console.projects.edit', $project))
            ->assertForbidden();
    }

    public function test_project_editor_validates_required_governance_and_evidence_fields(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProjectEditor)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.projects.store'), [
                'title' => '',
                'slug' => 'Not Valid',
                'approval' => ['submit_for_review' => true],
            ])
            ->assertSessionHasErrors([
                'project_category_id',
                'project_status_id',
                'title',
                'slug',
                'barangay',
                'source_url',
                'source_record.title',
                'approval.change_summary',
            ]);
    }

    public function test_project_editor_persists_project_source_and_evidence_records(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        [$category, $status, $partner] = $this->projectTaxonomy();

        $this->actingAs($staff)
            ->post(route('admin-console.projects.store'), $this->validProjectPayload($category, $status, $partner))
            ->assertRedirect();

        $project = Project::query()->where('slug', 'custom-egovph-hub')->firstOrFail();

        $this->assertSame($staff->id, $project->content_owner_id);
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Custom eGovPH Serbisyo Hub',
            'barangay' => 'Citywide',
            'source_url' => 'https://egov.gov.ph/',
        ]);
        $this->assertDatabaseHas('project_evidences', [
            'project_id' => $project->id,
            'title' => 'Launch documentation',
            'source_url' => 'https://egov.gov.ph/',
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $project->getMorphClass(),
            'sourceable_id' => $project->id,
            'title' => 'eGovPH official portal',
            'source_type' => SourceType::PrimaryOfficial->value,
            'verified_by_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => $project->getMorphClass(),
            'auditable_id' => $project->id,
            'event' => 'project.created',
        ]);
    }

    public function test_project_editor_updates_nested_evidence_records_transactionally(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        [$category, $status, $partner] = $this->projectTaxonomy();
        $project = Project::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->for($partner, 'primaryPartner')
            ->create([
                'title' => 'Old Project Title',
                'slug' => 'old-project-title',
                'source_url' => 'https://example.com/old-source',
                'content_owner_id' => $staff->id,
            ]);
        $evidence = ProjectEvidence::factory()->for($project)->create([
            'title' => 'Old evidence',
        ]);

        $payload = $this->validProjectPayload($category, $status, $partner, [
            'title' => 'Updated Project Title',
            'slug' => 'updated-project-title',
            'evidences' => [
                [
                    'title' => 'Updated evidence',
                    'source_label' => 'Official project source',
                    'source_url' => 'https://egov.gov.ph/',
                    'caption' => 'Updated verified caption.',
                    'alt_text' => 'Residents receiving digital service support.',
                    'rights_note' => 'District Office approved internal copy.',
                    'media_path' => null,
                    'verified_at' => now()->subDay()->toDateString(),
                ],
                [
                    'title' => 'Partner coordination note',
                    'source_label' => 'Partner coordination record',
                    'source_url' => 'https://egov.gov.ph/',
                    'caption' => 'Partner coordination record verified by staff.',
                    'alt_text' => 'Project coordination desk.',
                    'rights_note' => 'No private resident data is shown.',
                    'media_path' => null,
                    'verified_at' => now()->subDay()->toDateString(),
                ],
            ],
        ]);

        $this->actingAs($staff)
            ->put(route('admin-console.projects.update', $project), $payload)
            ->assertRedirect(route('admin-console.projects.show', $project));

        $project->refresh();

        $this->assertSame('Updated Project Title', $project->title);
        $this->assertSame('updated-project-title', $project->slug);
        $this->assertSame(2, $project->evidences()->count());
        $this->assertSoftDeleted('project_evidences', ['id' => $evidence->id]);
        $this->assertDatabaseHas('project_evidences', [
            'project_id' => $project->id,
            'title' => 'Partner coordination note',
        ]);
    }

    public function test_project_editor_can_submit_saved_project_to_approval_workflow(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        [$category, $status, $partner] = $this->projectTaxonomy();

        $payload = $this->validProjectPayload($category, $status, $partner, [
            'approval' => [
                'submit_for_review' => true,
                'change_summary' => 'Created the project record, verified official source, and added media-rights notes.',
                'is_fast_track' => false,
                'valid_until' => now()->addMonth()->toDateString(),
            ],
        ]);

        $this->actingAs($staff)
            ->post(route('admin-console.projects.store'), $payload)
            ->assertRedirect();

        $project = Project::query()->where('slug', 'custom-egovph-hub')->firstOrFail();
        $approval = ContentApproval::query()
            ->where('approvable_type', $project->getMorphClass())
            ->where('approvable_id', $project->id)
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

    public function test_project_console_filters_by_search_status_category_and_barangay(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        [$digital, $ongoing, $partner] = $this->projectTaxonomy();
        $infra = ProjectCategory::factory()->create(['name' => 'Infrastructure']);
        $completed = ProjectStatus::factory()->create([
            'value' => ProjectStatusValue::Completed,
            'label' => 'Completed',
            'tone' => ProjectStatusTone::Completed,
        ]);

        Project::factory()
            ->for($digital, 'category')
            ->for($ongoing, 'status')
            ->for($partner, 'primaryPartner')
            ->create([
                'title' => 'eGovPH Serbisyo Hub',
                'barangay' => 'Citywide',
                'location' => 'San Juan City Hall',
            ]);
        Project::factory()
            ->for($infra, 'category')
            ->for($completed, 'status')
            ->create([
                'title' => 'Road Improvement',
                'barangay' => 'Barangay Batis',
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.projects.index', [
                'search' => 'eGovPH',
                'status' => $ongoing->id,
                'category' => $digital->id,
                'barangay' => 'Citywide',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Index')
                ->where('projects.data.0.title', 'eGovPH Serbisyo Hub')
                ->where('projects.data.0.category.name', 'Digital Government')
                ->where('projects.data.0.barangay', 'Citywide')
                ->where('projects.meta.total', 1)
                ->where('filters.search', 'eGovPH')
                ->where('filters.category', (string) $digital->id));
    }

    public function test_project_dossier_exposes_source_approval_evidence_and_fallback_status(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        [$category, $status, $partner] = $this->projectTaxonomy();
        $project = Project::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->for($partner, 'primaryPartner')
            ->create([
                'title' => 'eGovPH Serbisyo Hub',
                'source_label' => 'eGovPH official portal',
                'source_url' => 'https://egov.gov.ph/',
                'content_owner_id' => $staff->id,
                'approved_by_id' => $reviewer->id,
                'review_due_at' => now()->addDays(20),
            ]);
        ProjectEvidence::factory()->for($project)->for($reviewer, 'verifier')->create([
            'title' => 'Launch documentation',
            'source_url' => 'https://egov.gov.ph/',
        ]);
        SourceRecord::factory()
            ->for($project, 'sourceable')
            ->for($staff, 'contentOwner')
            ->for($reviewer, 'verifier')
            ->create([
                'title' => 'Official eGovPH source',
                'url' => 'https://egov.gov.ph/',
            ]);
        $approval = ContentApproval::factory()
            ->for($project, 'approvable')
            ->for($staff, 'submitter')
            ->for($reviewer, 'reviewer')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Verified source, evidence, and partner attribution.',
                'revision_number' => 2,
                'submitted_at' => now(),
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.projects.show', $project))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Projects/Show')
                ->where('project.title', 'eGovPH Serbisyo Hub')
                ->where('project.source.recordTitle', 'Official eGovPH source')
                ->where('project.evidences.0.title', 'Launch documentation')
                ->where('project.governance.contentOwner', $staff->name)
                ->where('project.governance.approval.id', $approval->id)
                ->where('project.governance.approval.href', route('admin-console.content-approvals.show', $approval))
                ->where('project.governance.readiness.passed', true)
                ->where('project.can.update', true)
                ->where('filamentFallbackUrl', FilamentProjectResource::getUrl('edit', ['record' => $project])));
    }

    public function test_command_palette_search_uses_custom_project_route_after_module_migration(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $project = Project::factory()->create(['title' => 'Custom Searchable Project']);

        $this->actingAs($staff)
            ->getJson(route('admin-console.search', ['term' => 'Custom Searchable']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Project',
                'title' => 'Custom Searchable Project',
                'href' => route('admin-console.projects.show', $project),
                'usesLegacyFallback' => false,
            ]);
    }

    /**
     * @return array{0: ProjectCategory, 1: ProjectStatus, 2: ProjectPartner}
     */
    private function projectTaxonomy(): array
    {
        $category = ProjectCategory::factory()->create(['name' => 'Digital Government']);
        $status = ProjectStatus::factory()->create([
            'value' => ProjectStatusValue::Ongoing,
            'label' => 'Active / ongoing service',
            'tone' => ProjectStatusTone::Ongoing,
        ]);
        $partner = ProjectPartner::factory()->create(['name' => 'DICT / eGovPH and city partners']);

        return [$category, $status, $partner];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validProjectPayload(
        ProjectCategory $category,
        ProjectStatus $status,
        ProjectPartner $partner,
        array $overrides = [],
    ): array {
        $payload = [
            'project_category_id' => $category->id,
            'project_status_id' => $status->id,
            'primary_partner_id' => $partner->id,
            'title' => 'Custom eGovPH Serbisyo Hub',
            'slug' => 'custom-egovph-hub',
            'barangay' => 'Citywide',
            'location' => 'San Juan City Hall',
            'responsible_office' => 'District Office digital services coordination desk',
            'partner_label' => 'DICT / eGovPH and city partners',
            'summary' => 'Plain-language project record for digital government service access in San Juan.',
            'evidence_summary' => 'Evidence is based on official eGovPH source material and District Office review notes.',
            'impact_note' => 'Supports residents who need guided access to online government services.',
            'image_guidance' => 'Use only approved images that do not show private citizen records or sensitive screens.',
            'source_label' => 'eGovPH official portal',
            'source_url' => 'https://egov.gov.ph/',
            'verified_at' => now()->subDay()->toDateString(),
            'review_due_at' => now()->addMonth()->toDateString(),
            'published_at' => null,
            'sort_order' => 3,
            'source_record' => [
                'title' => 'eGovPH official portal',
                'url' => 'https://egov.gov.ph/',
                'source_type' => SourceType::PrimaryOfficial->value,
                'source_tier' => 'tier_1',
                'public_status' => 'published',
                'description' => 'Official source reviewed by the project editor.',
                'verified_at' => now()->subDay()->toDateString(),
                'next_review_at' => now()->addMonth()->toDateString(),
            ],
            'evidences' => [
                [
                    'title' => 'Launch documentation',
                    'source_label' => 'eGovPH official portal',
                    'source_url' => 'https://egov.gov.ph/',
                    'caption' => 'Official project record reviewed for public reuse.',
                    'alt_text' => 'Digital services assistance desk for residents.',
                    'rights_note' => 'Approved for MVP staging as metadata only; upload parity remains restricted.',
                    'media_path' => null,
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
