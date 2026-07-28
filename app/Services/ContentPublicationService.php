<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Events\ContentPublished;
use App\Events\ContentScheduled;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ContentPublicationService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentGovernanceService $governance,
        private readonly ContentSnapshotService $snapshots,
    ) {}

    public function schedule(ContentApproval $approval, Carbon|string $scheduledFor, ?User $actor = null): ContentApproval
    {
        if ((bool) config('belzamora.governance.content_approval.pre_publish_validation', true)) {
            $this->governance->assertPublishable($approval);
        }

        DB::transaction(function () use ($approval, $scheduledFor, $actor): void {
            $approval->forceFill([
                'status' => ContentApprovalStatus::Scheduled,
                'reviewed_by_id' => $approval->reviewed_by_id ?? $actor?->id,
                'reviewed_at' => $approval->reviewed_at ?? now(),
                'approved_at' => $approval->approved_at ?? now(),
                'scheduled_for' => $scheduledFor,
                'rejection_reason' => null,
            ])->save();

            $this->snapshots->capture($approval->fresh(['approvable']) ?? $approval, ContentSnapshotType::Scheduled, $actor);

            $this->auditLog->record(
                event: 'content.scheduled',
                auditable: $approval,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $approval->id,
                    'approvable_type' => $approval->approvable_type,
                    'approvable_id' => $approval->approvable_id,
                    'scheduled_for' => (string) $approval->scheduled_for,
                ],
            );
        });

        ContentScheduled::dispatch($approval->fresh(['approvable']));

        return $approval->fresh(['approvable']) ?? $approval;
    }

    public function publish(ContentApproval $approval, ?User $actor = null, string $event = 'content.published'): ContentApproval
    {
        if ((bool) config('belzamora.governance.content_approval.pre_publish_validation', true)) {
            $this->governance->assertPublishable($approval);
        }

        DB::transaction(function () use ($approval, $actor, $event): void {
            $publishedAt = now();

            $approval->forceFill([
                'status' => ContentApprovalStatus::Published,
                'reviewed_by_id' => $approval->reviewed_by_id ?? $actor?->id,
                'reviewed_at' => $approval->reviewed_at ?? now(),
                'approved_at' => $approval->approved_at ?? now(),
                'published_at' => $publishedAt,
                'unpublished_at' => null,
                'archived_at' => null,
                'archive_reason' => null,
            ])->save();

            $approvable = $approval->approvable;

            if ($approvable instanceof Model) {
                $attributes = [];

                if (array_key_exists('published_at', $approvable->getAttributes())) {
                    $attributes['published_at'] = $publishedAt;
                }

                if ($actor instanceof User && array_key_exists('approved_by_id', $approvable->getAttributes())) {
                    $attributes['approved_by_id'] = $actor->id;
                }

                if ($attributes !== []) {
                    $approvable->forceFill($attributes)->save();
                }
            }

            $this->snapshots->capture($approval->fresh(['approvable']) ?? $approval, ContentSnapshotType::Published, $actor);

            $this->auditLog->record(
                event: $event,
                auditable: $approval,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $approval->id,
                    'approvable_type' => $approval->approvable_type,
                    'approvable_id' => $approval->approvable_id,
                    'scheduled_for' => (string) $approval->scheduled_for,
                    'published_at' => (string) $approval->published_at,
                ],
            );
        });

        ContentPublished::dispatch($approval->fresh(['approvable']));

        return $approval->fresh(['approvable']) ?? $approval;
    }

    public function archive(ContentApproval $approval, string $reason, ?User $actor = null): ContentApproval
    {
        return $this->unpublish($approval, $reason, $actor, ContentSnapshotType::Archived, 'content.archived');
    }

    public function unpublish(
        ContentApproval $approval,
        string $reason,
        ?User $actor = null,
        ContentSnapshotType $snapshotType = ContentSnapshotType::Unpublished,
        string $event = 'content.unpublished',
    ): ContentApproval {
        DB::transaction(function () use ($approval, $reason, $actor, $snapshotType, $event): void {
            $approval->loadMissing('approvable');
            $unpublishedAt = now();

            $approval->forceFill([
                'status' => ContentApprovalStatus::Archived,
                'archived_at' => $unpublishedAt,
                'unpublished_at' => $unpublishedAt,
                'archive_reason' => $reason,
            ])->save();

            $approvable = $approval->approvable;

            if ($approvable instanceof Model && array_key_exists('published_at', $approvable->getAttributes())) {
                $approvable->forceFill(['published_at' => null])->save();
            }

            $this->snapshots->capture($approval->fresh(['approvable']) ?? $approval, $snapshotType, $actor, $reason);

            $this->auditLog->record(
                event: $event,
                auditable: $approval,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $approval->id,
                    'approvable_type' => $approval->approvable_type,
                    'approvable_id' => $approval->approvable_id,
                    'archive_reason' => $reason,
                    'unpublished_at' => (string) $approval->unpublished_at,
                ],
            );
        });

        return $approval->fresh(['approvable']) ?? $approval;
    }

    public function rollback(ContentApproval $approval, ContentSnapshot $snapshot, ?User $actor = null, ?string $reason = null): ContentApproval
    {
        $this->snapshots->rollbackToSnapshot($approval, $snapshot, $actor, $reason);

        return $approval->fresh(['approvable', 'rollbackSnapshot']) ?? $approval;
    }
}
