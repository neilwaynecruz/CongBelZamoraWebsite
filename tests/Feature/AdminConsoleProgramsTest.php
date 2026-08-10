<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ProgramStatus;
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
}
