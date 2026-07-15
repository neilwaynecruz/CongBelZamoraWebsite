<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\ServiceGuideResource;
use App\Models\ContentApproval;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\ServiceGuide;
use App\Models\SourceRecord;
use App\Models\User;
use Database\Seeders\ProgramAndServiceGuideSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase7ProgramsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_and_service_guide_schema_exists(): void
    {
        expect(Schema::hasTable('program_categories'))->toBeTrue()
            ->and(Schema::hasTable('programs'))->toBeTrue()
            ->and(Schema::hasTable('program_requirements'))->toBeTrue()
            ->and(Schema::hasTable('program_updates'))->toBeTrue()
            ->and(Schema::hasTable('service_guides'))->toBeTrue()
            ->and(Schema::hasTable('service_guide_steps'))->toBeTrue()
            ->and(Schema::hasColumn('programs', 'source_url'))->toBeTrue()
            ->and(Schema::hasColumn('service_guides', 'review_due_at'))->toBeTrue();
    }

    public function test_program_models_cast_enums_and_keep_relationships(): void
    {
        $category = ProgramCategory::factory()->create();
        $program = Program::factory()
            ->for($category, 'category')
            ->create([
                'status' => ProgramStatus::Open,
                'responsible_office_type' => ResponsibleOfficeType::JointInitiative,
            ]);

        $program->requirements()->create([
            'group_title' => 'Identity and residency',
            'item_text' => 'Valid identification only through approved channels.',
            'sort_order' => 10,
        ]);

        expect($program->status)->toBe(ProgramStatus::Open)
            ->and($program->responsible_office_type)->toBe(ResponsibleOfficeType::JointInitiative)
            ->and($program->category->is($category))->toBeTrue()
            ->and($program->requirements)->toHaveCount(1);
    }

    public function test_seeded_content_has_source_and_approval_records(): void
    {
        $this->seed(ProgramAndServiceGuideSeeder::class);

        $program = Program::query()->where('slug', 'ched-unifast-tulong-dunong')->firstOrFail();
        $guide = ServiceGuide::query()->where('slug', 'education-assistance')->firstOrFail();

        expect($program->sourceRecords)->toHaveCount(1)
            ->and($program->approvalRecords)->toHaveCount(1)
            ->and($program->approvalRecords->first()->status)->toBe(ContentApprovalStatus::Published)
            ->and($guide->sourceRecords)->toHaveCount(1)
            ->and($guide->approvalRecords)->toHaveCount(1)
            ->and(SourceRecord::query()->count())->toBeGreaterThanOrEqual(4)
            ->and(ContentApproval::query()->count())->toBeGreaterThanOrEqual(4);
    }

    public function test_program_and_service_guide_policies_are_least_privilege(): void
    {
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $contentAdmin = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        $program = Program::factory()->create();
        $guide = ServiceGuide::factory()->create();

        expect($programOfficer->can('update', $program))->toBeTrue()
            ->and($contentAdmin->can('delete', $program))->toBeTrue()
            ->and($reviewer->can('view', $program))->toBeTrue()
            ->and($reviewer->can('approve', $program))->toBeTrue()
            ->and($reviewer->can('update', $program))->toBeFalse()
            ->and($auditor->can('view', $guide))->toBeTrue()
            ->and($auditor->can('update', $guide))->toBeFalse()
            ->and($inquiryOfficer->can('view', $program))->toBeFalse()
            ->and($inquiryOfficer->can('update', $guide))->toBeFalse();
    }

    public function test_filament_program_resources_are_role_gated(): void
    {
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();

        $this->actingAs($programOfficer)
            ->get(ProgramResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($programOfficer)
            ->get(ServiceGuideResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($inquiryOfficer)
            ->get(ProgramResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($inquiryOfficer)
            ->get(ServiceGuideResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_public_program_routes_use_backend_records_with_phase_6_contracts(): void
    {
        $this->seed(ProgramAndServiceGuideSeeder::class);

        $this->get(route('public.programs'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/Index')
                ->has('programs', 4)
                ->where('programs.0.title', 'CHED UniFAST Tulong Dunong')
                ->where('programs.0.status', ProgramStatus::Open->value)
                ->where('programs.0.responsibleOfficeType', ResponsibleOfficeType::JointInitiative->value)
                ->where('verifiedAt', 'July 12, 2026'));

        $this->get(route('public.programs.tulong-dunong'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/TulongDunong')
                ->where('program.title', 'CHED UniFAST Tulong Dunong Program')
                ->has('program.eligibility', 4)
                ->has('program.requirementGroups', 2)
                ->has('program.privacyNotes', 3));

        $this->get(route('public.programs.gabay'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Programs/Gabay')
                ->has('serviceOptions', 4)
                ->where('serviceOptions.0.id', 'education-assistance')
                ->where('serviceOptions.1.responsibleOfficeType', ResponsibleOfficeType::CityGovernment->value)
                ->where('verifiedAt', 'July 12, 2026'));
    }
}
