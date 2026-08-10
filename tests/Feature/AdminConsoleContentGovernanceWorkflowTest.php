<?php

namespace Tests\Feature;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Models\AuditLog;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\Program;
use App\Models\User;
use App\Services\ContentPublicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminConsoleContentGovernanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_content_moves_through_owned_review_schedule_publish_and_unpublish_workflow(): void
    {
        $editor = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $program = Program::factory()->unpublished()->create();
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Draft,
                'submitted_by_id' => $editor->id,
                'change_summary' => null,
            ]);

        $this->actingAs($editor)
            ->post(route('admin-console.content-approvals.submit', $approval), [
                'change_summary' => 'Updated eligibility wording using the verified official program source.',
            ])
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $approval->refresh();
        $this->assertSame(ContentApprovalStatus::ForReview, $approval->status);
        $this->assertSame($editor->id, $approval->submitted_by_id);
        $this->assertDatabaseHas('content_snapshots', [
            'content_approval_id' => $approval->id,
            'snapshot_type' => ContentSnapshotType::Submitted->value,
        ]);

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.reviewer.assign', $approval), [
                'reviewer_id' => $reviewer->id,
            ])
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.approve', $approval), [
                'change_summary' => 'Reviewed eligibility wording, source, owner, and verification dates.',
                'review_notes' => 'Plain-language copy is consistent with the official source.',
            ])
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $approval->refresh();
        $this->assertSame(ContentApprovalStatus::Approved, $approval->status);
        $this->assertSame($reviewer->id, $approval->reviewed_by_id);
        $this->assertNotNull($approval->approved_at);
        $this->assertDatabaseHas('content_snapshots', [
            'content_approval_id' => $approval->id,
            'snapshot_type' => ContentSnapshotType::Approved->value,
        ]);

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.schedule', $approval), [
                'scheduled_for' => now()->addHour()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $this->assertSame(ContentApprovalStatus::Scheduled, $approval->fresh()?->status);

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.publish', $approval))
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $this->assertSame(ContentApprovalStatus::Published, $approval->fresh()?->status);
        $this->assertNotNull($program->fresh()?->published_at);

        $this->actingAs($administrator)
            ->post(route('admin-console.content-approvals.unpublish', $approval), [
                'reason_key' => 'source_recheck',
                'note' => 'Awaiting confirmation of the revised application window.',
            ])
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $approval->refresh();
        $this->assertSame(ContentApprovalStatus::Archived, $approval->status);
        $this->assertNull($program->fresh()?->published_at);
        $this->assertStringContainsString('Temporarily unpublished for source recheck', (string) $approval->archive_reason);

        foreach ([
            'content.submitted_for_review',
            'content.reviewer_assigned',
            'content.approved',
            'content.scheduled',
            'content.published',
            'content.unpublished',
        ] as $event) {
            $this->assertDatabaseHas('audit_logs', [
                'event' => $event,
                'auditable_type' => $approval->getMorphClass(),
                'auditable_id' => $approval->id,
            ]);
        }
    }

    public function test_request_changes_withdrawal_and_rejection_are_state_guarded_and_audited(): void
    {
        $editor = User::factory()->adminRole(AdminRole::ProgramOfficer)->create();
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $program = Program::factory()->unpublished()->create();
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::ForReview,
                'submitted_by_id' => $editor->id,
                'submitted_at' => now(),
                'reviewed_by_id' => $reviewer->id,
            ]);

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.request-changes', $approval), [
                'review_notes' => 'Clarify the application deadline and replace the secondary source with the official advisory.',
            ])
            ->assertRedirect();

        $this->assertSame(ContentApprovalStatus::NeedsRevision, $approval->fresh()?->status);

        $this->actingAs($editor)
            ->post(route('admin-console.content-approvals.submit', $approval), [
                'change_summary' => 'Replaced the source and clarified the application deadline.',
            ])
            ->assertRedirect();

        $this->actingAs($editor)
            ->post(route('admin-console.content-approvals.cancel', $approval), [
                'reason' => 'Withdrawing the revision to correct an additional program requirement.',
            ])
            ->assertRedirect();

        $this->assertSame(ContentApprovalStatus::Draft, $approval->fresh()?->status);

        $this->actingAs($editor)
            ->post(route('admin-console.content-approvals.submit', $approval), [
                'change_summary' => 'Corrected the final requirement and retained the official source.',
            ])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.reject', $approval), [
                'rejection_reason' => 'The revision conflicts with the approved District Office program guidance.',
            ])
            ->assertRedirect();

        $this->assertSame(ContentApprovalStatus::Rejected, $approval->fresh()?->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'content.revision_requested']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'content.review_cancelled']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'content.rejected']);
    }

    public function test_reviewer_cannot_decide_another_reviewers_owned_record_without_reassignment(): void
    {
        $owner = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $otherReviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $approval = ContentApproval::factory()
            ->for(Program::factory()->unpublished(), 'approvable')
            ->create([
                'status' => ContentApprovalStatus::ForReview,
                'reviewed_by_id' => $owner->id,
                'submitted_at' => now(),
            ]);

        $this->actingAs($otherReviewer)
            ->post(route('admin-console.content-approvals.approve', $approval), [
                'change_summary' => 'Attempted decision without accountable ownership.',
            ])
            ->assertSessionHasErrors('action');

        $this->assertSame(ContentApprovalStatus::ForReview, $approval->fresh()?->status);
        $this->assertSame(0, AuditLog::query()->where('event', 'content.approved')->count());
    }

    public function test_publish_gate_blocks_incomplete_content_and_returns_actionable_validation(): void
    {
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $program = Program::factory()->unpublished()->create([
            'source_url' => null,
            'verified_at' => null,
            'content_owner_id' => null,
        ]);
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'reviewed_by_id' => $reviewer->id,
                'approved_at' => now(),
                'change_summary' => 'Approved record with intentionally incomplete governance fields.',
            ]);

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.publish', $approval))
            ->assertSessionHasErrors('action');

        $this->assertSame(ContentApprovalStatus::Approved, $approval->fresh()?->status);
        $this->assertNull($program->fresh()?->published_at);
        $this->assertSame(0, ContentSnapshot::query()->where('snapshot_type', ContentSnapshotType::Published->value)->count());
    }

    public function test_only_senior_content_roles_can_unpublish_archive_or_restore_snapshots(): void
    {
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $auditor = User::factory()->adminRole(AdminRole::ReadOnlyAuditor)->create();
        $approval = ContentApproval::factory()
            ->for(Program::factory(), 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Published,
                'reviewed_by_id' => $reviewer->id,
                'published_at' => now(),
            ]);

        $this->actingAs($reviewer)
            ->post(route('admin-console.content-approvals.archive', $approval), [
                'reason_key' => 'superseded',
            ])
            ->assertForbidden();

        $this->actingAs($auditor)
            ->post(route('admin-console.content-approvals.reviewer.assign', $approval), [
                'reviewer_id' => $reviewer->id,
            ])
            ->assertForbidden();

        $this->assertSame(ContentApprovalStatus::Published, $approval->fresh()?->status);
    }

    public function test_safe_rollback_restores_only_a_governed_snapshot_and_preserves_publication_state(): void
    {
        $administrator = User::factory()->adminRole(AdminRole::ContentAdministrator)->create();
        $program = Program::factory()->unpublished()->create(['title' => 'Original verified title']);
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::Approved,
                'reviewed_by_id' => $administrator->id,
                'approved_at' => now(),
                'change_summary' => 'Original verified title approved for publication.',
            ]);

        app(ContentPublicationService::class)->publish($approval, $administrator);
        $publishedSnapshot = ContentSnapshot::query()
            ->where('snapshot_type', ContentSnapshotType::Published->value)
            ->firstOrFail();

        $program->forceFill(['title' => 'Unwanted later title'])->save();

        $this->actingAs($administrator)
            ->post(route('admin-console.content-approvals.rollback', $approval), [
                'snapshot_id' => $publishedSnapshot->id,
                'reason' => 'Restore the previously published and verified official title.',
            ])
            ->assertRedirect(route('admin-console.content-approvals.show', $approval));

        $this->assertSame('Original verified title', $program->fresh()?->title);
        $this->assertNotNull($program->fresh()?->published_at);
        $this->assertSame(ContentApprovalStatus::Published, $approval->fresh()?->status);
        $this->assertDatabaseHas('audit_logs', ['event' => 'content.rollback']);

        $draftSnapshot = ContentSnapshot::factory()->create([
            'content_approval_id' => $approval->id,
            'approvable_type' => $program::class,
            'approvable_id' => $program->id,
            'version_number' => ContentSnapshot::query()->max('version_number') + 1,
            'snapshot_type' => ContentSnapshotType::Draft,
            'is_publishable' => true,
        ]);

        $this->actingAs($administrator)
            ->post(route('admin-console.content-approvals.rollback', $approval), [
                'snapshot_id' => $draftSnapshot->id,
                'reason' => 'This draft snapshot must be rejected by the rollback gate.',
            ])
            ->assertSessionHasErrors('action');
    }

    public function test_governance_dossier_exposes_role_safe_actions_readiness_comparison_and_history(): void
    {
        $reviewer = User::factory()->adminRole(AdminRole::ReviewerApprover)->create();
        $program = Program::factory()->unpublished()->create(['title' => 'Governed directory item']);
        $approval = ContentApproval::factory()
            ->for($program, 'approvable')
            ->create([
                'status' => ContentApprovalStatus::ForReview,
                'reviewed_by_id' => $reviewer->id,
                'submitted_at' => now(),
                'change_summary' => 'Prepared the governed directory item for review.',
            ]);

        $this->actingAs($reviewer)
            ->get(route('admin-console.content-approvals.show', $approval))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/ContentApprovals/Show')
                ->where('approval.recordTitle', 'Governed directory item')
                ->where('approval.readiness.passed', true)
                ->where('workflow.actions.approve.allowed', true)
                ->where('workflow.actions.archive.allowed', false)
                ->has('reviewerOptions')
                ->has('comparison')
                ->has('snapshots'));
    }
}
