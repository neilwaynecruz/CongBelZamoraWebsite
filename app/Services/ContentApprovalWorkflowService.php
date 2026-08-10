<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Events\ContentApprovalDecided;
use App\Events\ContentApprovalSubmitted;
use App\Models\ContentApproval;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContentApprovalWorkflowService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentSnapshotService $snapshots,
    ) {}

    public function assignReviewer(ContentApproval $approval, User $reviewer, User $actor): ContentApproval
    {
        return DB::transaction(function () use ($approval, $reviewer, $actor): ContentApproval {
            $locked = $this->lockApproval($approval);
            $oldReviewer = $locked->reviewed_by_id;

            $this->ensure(
                in_array($locked->status, [
                    ContentApprovalStatus::Draft,
                    ContentApprovalStatus::ForReview,
                    ContentApprovalStatus::NeedsRevision,
                    ContentApprovalStatus::Approved,
                    ContentApprovalStatus::Scheduled,
                ], true),
                'Reviewer ownership cannot be changed after publication, rejection, or archive.',
            );
            $this->ensure(
                $reviewer->is_active && $this->hasConfiguredRole($reviewer, 'review'),
                'Select an active staff member authorized to review public content.',
                'reviewer_id',
            );

            $locked->forceFill(['reviewed_by_id' => $reviewer->id])->save();

            $this->auditLog->record(
                event: 'content.reviewer_assigned',
                auditable: $locked,
                actor: $actor,
                oldValues: ['reviewed_by_id' => $oldReviewer],
                newValues: ['reviewed_by_id' => $reviewer->id],
                metadata: ['content_workflow' => true],
            );

            return $this->fresh($locked);
        });
    }

    public function submitForReview(ContentApproval $approval, User $actor, string $changeSummary): ContentApproval
    {
        $approval = DB::transaction(function () use ($approval, $actor, $changeSummary): ContentApproval {
            $locked = $this->lockApproval($approval);
            $oldStatus = $locked->status;

            $this->ensure(
                in_array($locked->status, [ContentApprovalStatus::Draft, ContentApprovalStatus::NeedsRevision], true),
                'Only draft or needs-revision content can be submitted for review.',
            );

            $locked->forceFill([
                'status' => ContentApprovalStatus::ForReview,
                'submitted_by_id' => $actor->id,
                'submitted_at' => now(),
                'reviewed_at' => null,
                'approved_at' => null,
                'scheduled_for' => null,
                'change_summary' => $changeSummary,
                'rejection_reason' => null,
            ])->save();

            $this->snapshots->capture(
                approval: $this->fresh($locked),
                type: ContentSnapshotType::Submitted,
                actor: $actor,
                changeSummary: $changeSummary,
            );

            $this->recordTransition('content.submitted_for_review', $locked, $actor, $oldStatus);

            return $this->fresh($locked);
        });

        ContentApprovalSubmitted::dispatch($approval, $actor);

        return $approval;
    }

    public function approve(
        ContentApproval $approval,
        User $actor,
        string $changeSummary,
        ?string $reviewNotes = null,
    ): ContentApproval {
        $approval = DB::transaction(function () use ($approval, $actor, $changeSummary, $reviewNotes): ContentApproval {
            $locked = $this->lockApproval($approval);
            $oldStatus = $locked->status;

            $this->ensure($locked->status === ContentApprovalStatus::ForReview, 'Only content awaiting review can be approved.');
            $this->ensureReviewerOwnership($locked, $actor);

            $locked->forceFill([
                'status' => ContentApprovalStatus::Approved,
                'reviewed_by_id' => $actor->id,
                'reviewed_at' => now(),
                'approved_at' => now(),
                'change_summary' => $changeSummary,
                'review_notes' => $reviewNotes,
                'rejection_reason' => null,
            ])->save();

            $this->snapshots->capture(
                approval: $this->fresh($locked),
                type: ContentSnapshotType::Approved,
                actor: $actor,
                changeSummary: $changeSummary,
            );

            $this->recordTransition('content.approved', $locked, $actor, $oldStatus);

            return $this->fresh($locked);
        });

        ContentApprovalDecided::dispatch($approval, ContentApprovalStatus::Approved, $actor);

        return $approval;
    }

    public function requestChanges(ContentApproval $approval, User $actor, string $reviewNotes): ContentApproval
    {
        $approval = DB::transaction(function () use ($approval, $actor, $reviewNotes): ContentApproval {
            $locked = $this->lockApproval($approval);
            $oldStatus = $locked->status;

            $this->ensure($locked->status === ContentApprovalStatus::ForReview, 'Only content awaiting review can be returned for revision.');
            $this->ensureReviewerOwnership($locked, $actor);

            $locked->forceFill([
                'status' => ContentApprovalStatus::NeedsRevision,
                'reviewed_by_id' => $actor->id,
                'reviewed_at' => now(),
                'approved_at' => null,
                'scheduled_for' => null,
                'review_notes' => $reviewNotes,
                'rejection_reason' => null,
            ])->save();

            $this->recordTransition('content.revision_requested', $locked, $actor, $oldStatus);

            return $this->fresh($locked);
        });

        ContentApprovalDecided::dispatch($approval, ContentApprovalStatus::NeedsRevision, $actor);

        return $approval;
    }

    public function reject(ContentApproval $approval, User $actor, string $reason): ContentApproval
    {
        $approval = DB::transaction(function () use ($approval, $actor, $reason): ContentApproval {
            $locked = $this->lockApproval($approval);
            $oldStatus = $locked->status;

            $this->ensure($locked->status === ContentApprovalStatus::ForReview, 'Only content awaiting review can be rejected.');
            $this->ensureReviewerOwnership($locked, $actor);

            $locked->forceFill([
                'status' => ContentApprovalStatus::Rejected,
                'reviewed_by_id' => $actor->id,
                'reviewed_at' => now(),
                'approved_at' => null,
                'scheduled_for' => null,
                'rejection_reason' => $reason,
            ])->save();

            $this->recordTransition('content.rejected', $locked, $actor, $oldStatus);

            return $this->fresh($locked);
        });

        ContentApprovalDecided::dispatch($approval, ContentApprovalStatus::Rejected, $actor);

        return $approval;
    }

    public function cancel(ContentApproval $approval, User $actor, string $reason): ContentApproval
    {
        return DB::transaction(function () use ($approval, $actor, $reason): ContentApproval {
            $locked = $this->lockApproval($approval);
            $oldStatus = $locked->status;

            $targetStatus = match ($locked->status) {
                ContentApprovalStatus::ForReview => ContentApprovalStatus::Draft,
                ContentApprovalStatus::Scheduled => ContentApprovalStatus::Approved,
                default => null,
            };

            $this->ensure(
                $targetStatus instanceof ContentApprovalStatus,
                'Only an active review submission or scheduled publication can be cancelled.',
            );

            $locked->forceFill([
                'status' => $targetStatus,
                'scheduled_for' => null,
                'reviewed_at' => $targetStatus === ContentApprovalStatus::Draft ? null : $locked->reviewed_at,
                'approved_at' => $targetStatus === ContentApprovalStatus::Draft ? null : $locked->approved_at,
                'review_notes' => $reason,
                'rejection_reason' => null,
            ])->save();

            $this->auditLog->record(
                event: $oldStatus === ContentApprovalStatus::Scheduled
                    ? 'content.schedule_cancelled'
                    : 'content.review_cancelled',
                auditable: $locked,
                actor: $actor,
                oldValues: ['status' => $oldStatus->value],
                newValues: ['status' => $targetStatus->value, 'reason_recorded' => true],
                metadata: ['content_workflow' => true],
            );

            return $this->fresh($locked);
        });
    }

    private function ensureReviewerOwnership(ContentApproval $approval, User $actor): void
    {
        $this->ensure(
            $approval->reviewed_by_id === null || $approval->reviewed_by_id === $actor->id || $actor->hasAdminRole(AdminRole::SuperAdministrator),
            'This review is assigned to another reviewer. Reassign ownership before recording a decision.',
        );
    }

    private function lockApproval(ContentApproval $approval): ContentApproval
    {
        return ContentApproval::query()
            ->with('approvable')
            ->lockForUpdate()
            ->findOrFail($approval->getKey());
    }

    private function fresh(ContentApproval $approval): ContentApproval
    {
        return $approval->fresh(['approvable', 'submitter', 'reviewer', 'snapshots.capturedBy']) ?? $approval;
    }

    private function recordTransition(
        string $event,
        ContentApproval $approval,
        User $actor,
        ContentApprovalStatus $oldStatus,
    ): void {
        $this->auditLog->record(
            event: $event,
            auditable: $approval,
            actor: $actor,
            oldValues: ['status' => $oldStatus->value],
            newValues: [
                'status' => $approval->status->value,
                'reviewed_by_id' => $approval->reviewed_by_id,
                'change_summary_recorded' => filled($approval->change_summary),
                'review_notes_recorded' => filled($approval->review_notes),
                'rejection_reason_recorded' => filled($approval->rejection_reason),
            ],
            metadata: ['content_workflow' => true],
        );
    }

    private function hasConfiguredRole(User $user, string $action): bool
    {
        $roles = config("belzamora.governance.workflow_roles.{$action}", []);

        return $user->admin_role instanceof AdminRole
            && is_array($roles)
            && in_array($user->admin_role->value, $roles, true);
    }

    private function ensure(bool $condition, string $message, string $field = 'action'): void
    {
        if (! $condition) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
