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
use Illuminate\Validation\ValidationException;

class ContentPublicationService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentGovernanceService $governance,
        private readonly ContentSnapshotService $snapshots,
    ) {}

    public function schedule(ContentApproval $approval, Carbon|string $scheduledFor, ?User $actor = null): ContentApproval
    {
        $approval = DB::transaction(function () use ($approval, $scheduledFor, $actor): ContentApproval {
            $locked = $this->lockApproval($approval);

            $this->ensure(
                in_array($locked->status, [ContentApprovalStatus::Approved, ContentApprovalStatus::Scheduled], true),
                'Only approved content can be scheduled for publication.',
            );

            if ((bool) config('belzamora.governance.content_approval.pre_publish_validation', true)) {
                $this->governance->assertPublishable($locked);
            }

            $locked->forceFill([
                'status' => ContentApprovalStatus::Scheduled,
                'reviewed_by_id' => $locked->reviewed_by_id ?? $actor?->id,
                'reviewed_at' => $locked->reviewed_at ?? now(),
                'approved_at' => $locked->approved_at ?? now(),
                'scheduled_for' => $scheduledFor,
                'rejection_reason' => null,
            ])->save();

            $this->snapshots->capture($this->fresh($locked), ContentSnapshotType::Scheduled, $actor);

            $this->auditLog->record(
                event: 'content.scheduled',
                auditable: $locked,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $locked->id,
                    'approvable_type' => $locked->approvable_type,
                    'approvable_id' => $locked->approvable_id,
                    'scheduled_for' => (string) $locked->scheduled_for,
                ],
            );

            return $this->fresh($locked);
        });

        ContentScheduled::dispatch($approval);

        return $approval;
    }

    public function publish(ContentApproval $approval, ?User $actor = null, string $event = 'content.published'): ContentApproval
    {
        $approval = DB::transaction(function () use ($approval, $actor, $event): ContentApproval {
            $locked = $this->lockApproval($approval);

            $this->ensure(
                in_array($locked->status, [ContentApprovalStatus::Approved, ContentApprovalStatus::Scheduled], true),
                'Only approved or scheduled content can be published.',
            );

            if ((bool) config('belzamora.governance.content_approval.pre_publish_validation', true)) {
                $this->governance->assertPublishable($locked);
            }

            $publishedAt = now();

            $locked->forceFill([
                'status' => ContentApprovalStatus::Published,
                'reviewed_by_id' => $locked->reviewed_by_id ?? $actor?->id,
                'reviewed_at' => $locked->reviewed_at ?? now(),
                'approved_at' => $locked->approved_at ?? now(),
                'published_at' => $publishedAt,
                'scheduled_for' => null,
                'unpublished_at' => null,
                'archived_at' => null,
                'archive_reason' => null,
            ])->save();

            $approvable = $locked->approvable;

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

            $this->snapshots->capture($this->fresh($locked), ContentSnapshotType::Published, $actor);

            $this->auditLog->record(
                event: $event,
                auditable: $locked,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $locked->id,
                    'approvable_type' => $locked->approvable_type,
                    'approvable_id' => $locked->approvable_id,
                    'scheduled_for' => (string) $locked->scheduled_for,
                    'published_at' => (string) $locked->published_at,
                ],
            );

            return $this->fresh($locked);
        });

        ContentPublished::dispatch($approval);

        return $approval;
    }

    public function archive(ContentApproval $approval, string $reason, ?User $actor = null): ContentApproval
    {
        return $this->removeFromPublication(
            $approval,
            $reason,
            $actor,
            ContentSnapshotType::Archived,
            'content.archived',
            false,
        );
    }

    public function unpublish(
        ContentApproval $approval,
        string $reason,
        ?User $actor = null,
        ContentSnapshotType $snapshotType = ContentSnapshotType::Unpublished,
        string $event = 'content.unpublished',
    ): ContentApproval {
        return $this->removeFromPublication($approval, $reason, $actor, $snapshotType, $event, true);
    }

    private function removeFromPublication(
        ContentApproval $approval,
        string $reason,
        ?User $actor,
        ContentSnapshotType $snapshotType,
        string $event,
        bool $mustBePublished,
    ): ContentApproval {
        return DB::transaction(function () use ($approval, $reason, $actor, $snapshotType, $event, $mustBePublished): ContentApproval {
            $locked = $this->lockApproval($approval);

            $this->ensure(
                $mustBePublished
                    ? $locked->status === ContentApprovalStatus::Published
                    : $locked->status !== ContentApprovalStatus::Archived,
                $mustBePublished
                    ? 'Only currently published content can be unpublished.'
                    : 'This content is already archived.',
            );

            $unpublishedAt = now();

            $locked->forceFill([
                'status' => ContentApprovalStatus::Archived,
                'archived_at' => $unpublishedAt,
                'unpublished_at' => $unpublishedAt,
                'scheduled_for' => null,
                'archive_reason' => $reason,
            ])->save();

            $approvable = $locked->approvable;

            if ($approvable instanceof Model && array_key_exists('published_at', $approvable->getAttributes())) {
                $approvable->forceFill(['published_at' => null])->save();
            }

            $this->snapshots->capture($this->fresh($locked), $snapshotType, $actor, $reason);

            $this->auditLog->record(
                event: $event,
                auditable: $locked,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $locked->id,
                    'approvable_type' => $locked->approvable_type,
                    'approvable_id' => $locked->approvable_id,
                    'archive_reason' => $reason,
                    'unpublished_at' => (string) $locked->unpublished_at,
                ],
            );

            return $this->fresh($locked);
        });
    }

    public function rollback(ContentApproval $approval, ContentSnapshot $snapshot, ?User $actor = null, ?string $reason = null): ContentApproval
    {
        $approval = DB::transaction(function () use ($approval, $snapshot, $actor, $reason): ContentApproval {
            $locked = $this->lockApproval($approval);

            $this->snapshots->rollbackToSnapshot($locked, $snapshot, $actor, $reason);

            return $this->fresh($locked);
        });

        return $approval;
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
        return $approval->fresh(['approvable', 'rollbackSnapshot']) ?? $approval;
    }

    private function ensure(bool $condition, string $message): void
    {
        if (! $condition) {
            throw ValidationException::withMessages(['action' => $message]);
        }
    }
}
