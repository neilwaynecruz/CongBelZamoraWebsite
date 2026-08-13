<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Enums\ProgramStatus;
use App\Enums\ResponsibleOfficeType;
use App\Enums\SourceType;
use App\Filament\Resources\ProgramResource;
use App\Models\ContentApproval;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleProgramsTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_console_is_policy_protected_and_navigation_is_role_aware(): void
    {
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $inquiryOfficer = User::factory()->adminRole(AdminRole::InquiryOfficer)->create();
        Program::factory()->create(['title' => 'Policy Protected Program']);

        $this->actingAs($programOfficer)
            ->get(route('admin-console.programs.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Index')
                ->where('adminConsole.user.role', AdminRole::ProgramOfficer->value)
                ->where('can.create', true)
                ->has('adminConsole.navigation'));

        $labels = collect(app(AdminConsoleNavigationService::class)->for($programOfficer))
            ->pluck('items')
            ->flatten(1)
            ->pluck('label');

        $this->assertTrue($labels->contains('Programs'));

        $this->actingAs($inquiryOfficer)
            ->get(route('admin-console.programs.index'))
            ->assertForbidden();
    }

    public function test_program_editor_create_and_edit_routes_are_policy_protected(): void
    {
        $programOfficer = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $category = ProgramCategory::factory()->create(['name' => 'Education']);
        $program = Program::factory()->for($category, 'category')->create([
            'title' => 'Editable Program',
        ]);

        $this->actingAs($programOfficer)
            ->get(route('admin-console.programs.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Edit')
                ->where('form.mode', 'create')
                ->where('form.options.categories.0.label', 'Education'));

        $this->actingAs($programOfficer)
            ->get(route('admin-console.programs.edit', $program))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Edit')
                ->where('form.mode', 'edit')
                ->where('form.values.title', 'Editable Program'));

        $this->actingAs($reviewer)
            ->get(route('admin-console.programs.create'))
            ->assertForbidden();

        $this->actingAs($reviewer)
            ->get(route('admin-console.programs.edit', $program))
            ->assertForbidden();
    }

    public function test_program_editor_validates_required_governance_fields(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();

        $this->actingAs($staff)
            ->post(route('admin-console.programs.store'), [
                'title' => '',
                'slug' => 'Not Valid',
                'href' => 'programs',
                'approval' => ['submit_for_review' => true],
            ])
            ->assertSessionHasErrors([
                'program_category_id',
                'title',
                'slug',
                'href',
                'source_url',
                'source_record.title',
                'approval.change_summary',
            ]);
    }

    public function test_program_editor_persists_program_source_requirements_and_updates(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $category = ProgramCategory::factory()->create(['name' => 'Education']);

        $this->actingAs($staff)
            ->post(route('admin-console.programs.store'), $this->validProgramPayload($category))
            ->assertRedirect();

        $program = Program::query()->where('slug', 'custom-tulong-dunong')->firstOrFail();

        $this->assertSame($staff->id, $program->content_owner_id);
        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'title' => 'Custom Tulong Dunong Guidance',
            'source_url' => 'https://ched.gov.ph/unifast/',
        ]);
        $this->assertDatabaseHas('program_requirements', [
            'program_id' => $program->id,
            'group_title' => 'Education assistance',
            'item_text' => 'Bring school enrollment confirmation.',
        ]);
        $this->assertDatabaseHas('program_updates', [
            'program_id' => $program->id,
            'title' => 'Application guidance updated',
            'source_url' => 'https://ched.gov.ph/unifast/',
        ]);
        $this->assertDatabaseHas('source_records', [
            'sourceable_type' => $program->getMorphClass(),
            'sourceable_id' => $program->id,
            'title' => 'CHED UniFAST public advisory',
            'source_type' => SourceType::PrimaryOfficial->value,
            'verified_by_id' => $staff->id,
        ]);
    }

    public function test_program_editor_updates_nested_records_transactionally(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $category = ProgramCategory::factory()->create(['name' => 'Education']);
        $program = Program::factory()
            ->for($category, 'category')
            ->create([
                'title' => 'Old Program Title',
                'slug' => 'old-program-title',
                'source_url' => 'https://example.com/old-source',
                'content_owner_id' => $staff->id,
            ]);
        $requirement = $program->requirements()->create([
            'group_title' => 'Old group',
            'item_text' => 'Old requirement',
            'sort_order' => 1,
        ]);

        $payload = $this->validProgramPayload($category, [
            'title' => 'Updated Program Title',
            'slug' => 'updated-program-title',
            'requirements' => [
                [
                    'group_title' => 'Updated group',
                    'item_text' => 'Updated requirement',
                    'note' => 'Bring clear copies only.',
                ],
                [
                    'group_title' => 'Updated group',
                    'item_text' => 'Keep reference number ready.',
                    'note' => null,
                ],
            ],
        ]);

        $this->actingAs($staff)
            ->put(route('admin-console.programs.update', $program), $payload)
            ->assertRedirect(route('admin-console.programs.show', $program));

        $program->refresh();

        $this->assertSame('Updated Program Title', $program->title);
        $this->assertSame('updated-program-title', $program->slug);
        $this->assertSame(2, $program->requirements()->count());
        $this->assertSoftDeleted('program_requirements', ['id' => $requirement->id]);
        $this->assertDatabaseHas('program_requirements', [
            'program_id' => $program->id,
            'item_text' => 'Keep reference number ready.',
        ]);
    }

    public function test_program_editor_can_submit_saved_program_to_approval_workflow(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $category = ProgramCategory::factory()->create(['name' => 'Education']);

        $payload = $this->validProgramPayload($category, [
            'approval' => [
                'submit_for_review' => true,
                'change_summary' => 'Created the program record, verified source, and added the first checklist.',
                'is_fast_track' => false,
                'valid_until' => now()->addMonth()->toDateString(),
            ],
        ]);

        $this->actingAs($staff)
            ->post(route('admin-console.programs.store'), $payload)
            ->assertRedirect();

        $program = Program::query()->where('slug', 'custom-tulong-dunong')->firstOrFail();
        $approval = ContentApproval::query()
            ->where('approvable_type', $program->getMorphClass())
            ->where('approvable_id', $program->id)
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

    public function test_program_console_filters_by_search_status_and_category(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $education = ProgramCategory::factory()->create(['name' => 'Education']);
        $health = ProgramCategory::factory()->create(['name' => 'Health']);

        Program::factory()->create([
            'program_category_id' => $education->id,
            'title' => 'Tulong Dunong Scholarship Desk',
            'status' => ProgramStatus::Open,
            'sector' => 'Education',
        ]);
        Program::factory()->create([
            'program_category_id' => $health->id,
            'title' => 'Medical Coordination Desk',
            'status' => ProgramStatus::Closed,
            'sector' => 'Health',
        ]);

        $this->actingAs($staff)
            ->get(route('admin-console.programs.index', [
                'search' => 'Tulong',
                'status' => ProgramStatus::Open->value,
                'category' => $education->id,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Index')
                ->where('programs.data.0.title', 'Tulong Dunong Scholarship Desk')
                ->where('programs.data.0.category.name', 'Education')
                ->where('programs.data.0.status.value', ProgramStatus::Open->value)
                ->where('programs.meta.total', 1)
                ->where('filters.search', 'Tulong')
                ->where('filters.category', (string) $education->id));
    }

    public function test_program_dossier_exposes_source_approval_and_readiness_without_writes(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $program = Program::factory()->create([
            'title' => 'CHED UniFAST Tulong Dunong Program',
            'source_label' => 'CHED UniFAST advisory',
            'source_url' => 'https://ched.gov.ph/unifast/',
            'content_owner_id' => $staff->id,
            'approved_by_id' => $reviewer->id,
            'review_due_at' => now()->addDays(20),
        ]);
        SourceRecord::factory()
            ->for($program, 'sourceable')
            ->for($staff, 'contentOwner')
            ->for($reviewer, 'verifier')
            ->create([
                'title' => 'Official CHED UniFAST source',
                'url' => 'https://ched.gov.ph/unifast/',
            ]);
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->for($staff, 'submitter')
            ->for($reviewer, 'reviewer')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'change_summary' => 'Verified source, owner, and program wording.',
                'revision_number' => 3,
                'submitted_at' => now(),
            ]);

        $this->actingAs($staff)
            ->get(route('admin-console.programs.show', $program))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Programs/Show')
                ->where('program.title', 'CHED UniFAST Tulong Dunong Program')
                ->where('program.source.recordTitle', 'Official CHED UniFAST source')
                ->where('program.governance.contentOwner', $staff->name)
                ->where('program.governance.approval.id', $approval->id)
                ->where('program.governance.approval.href', route('admin-console.content-approvals.show', $approval))
                ->where('program.governance.readiness.passed', true)
                ->where('program.can.update', true)
                ->where('filamentFallbackUrl', null));

        $superAdministrator = User::factory()->adminRole(AdminRole::SuperAdministrator)->create();

        $this->actingAs($superAdministrator)
            ->get(route('admin-console.programs.show', $program))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('filamentFallbackUrl', ProgramResource::getUrl('edit', ['record' => $program])));
    }

    public function test_command_palette_search_uses_custom_program_route_after_module_migration(): void
    {
        $staff = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $program = Program::factory()->create(['title' => 'Custom Searchable Program']);

        $this->actingAs($staff)
            ->getJson(route('admin-console.search', ['term' => 'Custom Searchable']))
            ->assertOk()
            ->assertJsonFragment([
                'type' => 'Program',
                'title' => 'Custom Searchable Program',
                'href' => route('admin-console.programs.show', $program),
                'usesLegacyFallback' => false,
            ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validProgramPayload(ProgramCategory $category, array $overrides = []): array
    {
        $payload = [
            'program_category_id' => $category->id,
            'title' => 'Custom Tulong Dunong Guidance',
            'slug' => 'custom-tulong-dunong',
            'href' => '/programs/education/tulong-dunong',
            'sector' => 'Education',
            'status' => ProgramStatus::Open->value,
            'summary' => 'Plain-language guidance for education assistance inquiries.',
            'description' => 'Staff-maintained guidance describing current education assistance coordination.',
            'responsible_office_type' => ResponsibleOfficeType::DistrictOffice->value,
            'responsible_office_label' => 'Education and Scholarship Assistance Desk',
            'partner_agency' => 'CHED UniFAST',
            'beneficiary_group' => 'Students and parents',
            'application_period' => 'Confirm the active window with the official source.',
            'benefit' => 'Guidance and routing for education assistance questions.',
            'source_label' => 'CHED UniFAST advisory',
            'source_url' => 'https://ched.gov.ph/unifast/',
            'verified_at' => now()->subDay()->toDateString(),
            'review_due_at' => now()->addMonth()->toDateString(),
            'published_at' => null,
            'is_featured' => true,
            'sort_order' => 4,
            'source_record' => [
                'title' => 'CHED UniFAST public advisory',
                'url' => 'https://ched.gov.ph/unifast/',
                'source_type' => SourceType::PrimaryOfficial->value,
                'source_tier' => 'tier_1',
                'public_status' => 'published',
                'description' => 'Official source reviewed by the program editor.',
                'verified_at' => now()->subDay()->toDateString(),
                'next_review_at' => now()->addMonth()->toDateString(),
            ],
            'requirements' => [
                [
                    'group_title' => 'Education assistance',
                    'item_text' => 'Bring school enrollment confirmation.',
                    'note' => 'Do not upload private student records through public forms.',
                ],
            ],
            'updates' => [
                [
                    'title' => 'Application guidance updated',
                    'summary' => 'Staff reviewed the public guidance and source reference.',
                    'status' => ProgramStatus::Open->value,
                    'source_label' => 'CHED UniFAST advisory',
                    'source_url' => 'https://ched.gov.ph/unifast/',
                    'verified_at' => now()->subDay()->toDateString(),
                    'published_at' => null,
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
