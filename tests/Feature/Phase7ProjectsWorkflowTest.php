<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ProjectStatusTone;
use App\Enums\ProjectStatusValue;
use App\Filament\Resources\ProjectEvidenceResource;
use App\Filament\Resources\ProjectResource;
use App\Models\ContentApproval;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectEvidence;
use App\Models\ProjectPartner;
use App\Models\ProjectStatus;
use App\Models\SourceRecord;
use App\Models\User;
use Database\Seeders\ProjectRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase7ProjectsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_schema_exists(): void
    {
        expect(Schema::hasTable('project_categories'))->toBeTrue()
            ->and(Schema::hasTable('project_statuses'))->toBeTrue()
            ->and(Schema::hasTable('project_partners'))->toBeTrue()
            ->and(Schema::hasTable('projects'))->toBeTrue()
            ->and(Schema::hasTable('project_evidences'))->toBeTrue()
            ->and(Schema::hasColumn('projects', 'barangay'))->toBeTrue()
            ->and(Schema::hasColumn('projects', 'review_due_at'))->toBeTrue()
            ->and(Schema::hasColumn('project_evidences', 'rights_note'))->toBeTrue();
    }

    public function test_project_models_cast_enums_and_keep_relationships(): void
    {
        $category = ProjectCategory::factory()->create(['name' => 'Digital Government']);
        $status = ProjectStatus::factory()->create([
            'value' => ProjectStatusValue::Ongoing,
            'label' => 'Active / ongoing service',
            'tone' => ProjectStatusTone::Ongoing,
        ]);
        $partner = ProjectPartner::factory()->create(['name' => 'DICT / eGovPH and city partners']);
        $project = Project::factory()
            ->for($category, 'category')
            ->for($status, 'status')
            ->for($partner, 'primaryPartner')
            ->create();

        ProjectEvidence::factory()
            ->for($project)
            ->create(['rights_note' => 'Approved original copy required.']);

        expect($project->status->value)->toBe(ProjectStatusValue::Ongoing)
            ->and($project->status->tone)->toBe(ProjectStatusTone::Ongoing)
            ->and($project->category->is($category))->toBeTrue()
            ->and($project->primaryPartner->is($partner))->toBeTrue()
            ->and($project->evidences)->toHaveCount(1);
    }

    public function test_seeded_projects_have_evidence_source_and_approval_records(): void
    {
        $this->seed(ProjectRecordSeeder::class);

        $project = Project::query()->where('slug', 'solo-parent-educational-assistance')->firstOrFail();

        expect($project->evidences)->toHaveCount(1)
            ->and($project->sourceRecords)->toHaveCount(1)
            ->and($project->approvalRecords)->toHaveCount(1)
            ->and($project->approvalRecords->first()->status)->toBe(ContentApprovalStatus::Published)
            ->and(SourceRecord::query()->where('sourceable_type', Project::class)->count())->toBe(5)
            ->and(ContentApproval::query()->where('approvable_type', Project::class)->count())->toBe(5);
    }

    public function test_project_policies_are_least_privilege(): void
    {
        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $legislativeEditor = User::factory()->adminRole(AdminRole::LegislativeEditor)->create();
        $project = Project::factory()->create();
        $evidence = ProjectEvidence::factory()->for($project)->create();

        expect($projectEditor->can('update', $project))->toBeTrue()
            ->and($projectEditor->can('update', $evidence))->toBeTrue()
            ->and($contentAdmin->can('delete', $project))->toBeTrue()
            ->and($reviewer->can('view', $project))->toBeTrue()
            ->and($reviewer->can('approve', $project))->toBeTrue()
            ->and($reviewer->can('update', $project))->toBeFalse()
            ->and($auditor->can('view', $project))->toBeTrue()
            ->and($auditor->can('update', $project))->toBeFalse()
            ->and($inquiryOfficer->can('view', $project))->toBeFalse()
            ->and($legislativeEditor->can('update', $project))->toBeFalse();
    }

    public function test_filament_project_resources_are_emergency_only_while_custom_console_is_role_gated(): void
    {
        $projectEditor = User::factory()->adminRole(AdminRole::ProjectEditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $this->actingAs($projectEditor)
            ->get(ProjectResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($projectEditor)
            ->get(ProjectEvidenceResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($projectEditor)
            ->get(route('admin-console.projects.index'))
            ->assertOk();

        $this->actingAs($inquiryOfficer)
            ->get(ProjectResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_public_projects_route_uses_backend_records_with_phase_6_contract(): void
    {
        $this->seed(ProjectRecordSeeder::class);

        $this->get(route('public.projects'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Projects/Index')
                ->has('projects', 5)
                ->where('projects.0.slug', 'solo-parent-educational-assistance')
                ->where('projects.0.status', ProjectStatusValue::Completed->value)
                ->where('projects.1.statusLabel', 'Active / ongoing service')
                ->where('projects.1.statusTone', ProjectStatusTone::Ongoing->value)
                ->has('barangayOptions')
                ->has('statusOptions')
                ->has('categoryOptions')
                ->where('initialFilters.status', 'All statuses')
                ->where('verifiedAt', 'July 12, 2026'));

        $this->get(route('public.projects', ['status' => 'ongoing']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Projects/Index')
                ->where('initialFilters.status', 'Active / ongoing service'));
    }
}
