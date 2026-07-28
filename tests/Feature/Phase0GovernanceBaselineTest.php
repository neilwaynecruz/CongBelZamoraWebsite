<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\Program;
use App\Models\PublicPage;
use App\Services\ContentGovernanceService;
use App\Services\ContentPublicationService;
use App\Support\ContentGovernanceException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase0GovernanceBaselineTest extends TestCase
{
    use RefreshDatabase;

    public function test_office_wording_and_contact_policy_are_configured(): void
    {
        $office = config('belzamora.office');

        $this->assertSame('Digital District Office for San Juan City', $office['portal_name']);
        $this->assertSame('Lone District of San Juan City', $office['district']);
        $this->assertNotEmpty($office['public_service_statement']);
        $this->assertNotEmpty($office['response_time_policy']);
        $this->assertSame('0976-062-3339', $office['contact_channels']['phone']);
        $this->assertSame('/contact', $office['contact_channels']['contact_form_path']);
    }

    public function test_governance_matrix_covers_every_phase_zero_public_module(): void
    {
        $modules = config('belzamora.governance.modules');

        foreach (['programs', 'projects', 'news', 'legislation', 'public_pages'] as $key) {
            $this->assertArrayHasKey($key, $modules);
            $this->assertTrue(class_exists($modules[$key]['model']));
            $this->assertGreaterThan(0, $modules[$key]['review_cadence_days']);
            $this->assertNotEmpty($modules[$key]['required_publish_fields']);
            $this->assertNotEmpty($modules[$key]['source_policy']);
        }
    }

    public function test_governance_workflow_roles_are_declared_for_core_actions(): void
    {
        $roles = config('belzamora.governance.workflow_roles');

        foreach (['draft', 'review', 'approve', 'schedule', 'publish', 'archive'] as $action) {
            $this->assertArrayHasKey($action, $roles);
            $this->assertContains(AdminRole::SuperAdministrator->value, $roles[$action]);
        }

        $this->assertContains(AdminRole::ReviewerApprover->value, $roles['review']);
        $this->assertContains(AdminRole::ReviewerApprover->value, $roles['publish']);
        $this->assertNotContains(AdminRole::ReadOnlyAuditor->value, $roles['publish']);
    }

    public function test_valid_public_page_passes_pre_publish_governance_validation(): void
    {
        $page = PublicPage::factory()->create(['published_at' => null]);
        $approval = ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Approved,
        ]);

        app(ContentPublicationService::class)->publish($approval);

        $this->assertNotNull($page->fresh()?->published_at);
        $this->assertSame(ContentApprovalStatus::Published, $approval->fresh()?->status);
    }

    public function test_program_missing_required_source_is_blocked_before_publish(): void
    {
        $program = Program::factory()->unpublished()->create([
            'source_url' => null,
        ]);
        $approval = ContentApproval::factory()->create([
            'approvable_type' => $program->getMorphClass(),
            'approvable_id' => $program->id,
            'status' => ContentApprovalStatus::Approved,
        ]);

        try {
            app(ContentPublicationService::class)->publish($approval);
            $this->fail('Expected content governance validation to block publication.');
        } catch (ContentGovernanceException $exception) {
            $this->assertStringContainsString('source_url', implode(' ', $exception->issues()));
        }

        $this->assertNull($program->fresh()?->published_at);
        $this->assertSame(ContentApprovalStatus::Approved, $approval->fresh()?->status);
    }

    public function test_scheduling_uses_the_same_pre_publish_governance_validation(): void
    {
        $program = Program::factory()->unpublished()->create([
            'verified_at' => now()->addDay(),
        ]);
        $approval = ContentApproval::factory()->create([
            'approvable_type' => $program->getMorphClass(),
            'approvable_id' => $program->id,
            'status' => ContentApprovalStatus::Approved,
        ]);

        $this->expectException(ContentGovernanceException::class);

        app(ContentPublicationService::class)->schedule($approval, now()->addHour());
    }

    public function test_uncertain_public_statement_requires_source_or_review_note(): void
    {
        $page = PublicPage::factory()->create([
            'source_url' => null,
            'metadata' => ['contains_uncertain_public_statement' => true],
        ]);
        $approval = ContentApproval::factory()->create([
            'approvable_type' => $page->getMorphClass(),
            'approvable_id' => $page->id,
            'status' => ContentApprovalStatus::Approved,
        ]);

        $issues = app(ContentGovernanceService::class)->blockingIssues($approval);

        $this->assertTrue(collect($issues)->contains(
            fn (string $issue): bool => str_contains($issue, 'uncertain public statement')
        ));
    }

    public function test_governance_readiness_checklist_explains_required_rules(): void
    {
        $program = Program::factory()->unpublished()->create();
        $approval = ContentApproval::factory()->create([
            'approvable_type' => $program->getMorphClass(),
            'approvable_id' => $program->id,
            'status' => ContentApprovalStatus::Approved,
        ]);

        $checklist = app(ContentGovernanceService::class)->readinessChecklist($approval);

        $this->assertTrue(collect($checklist)->contains(fn (string $line): bool => str_contains($line, 'Module: Programs')));
        $this->assertTrue(collect($checklist)->contains(fn (string $line): bool => str_contains($line, 'Required publish fields')));
        $this->assertTrue(collect($checklist)->contains(fn (string $line): bool => str_contains($line, 'Workflow roles')));
    }
}
