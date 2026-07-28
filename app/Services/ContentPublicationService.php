<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Events\ContentPublished;
use App\Events\ContentScheduled;
use App\Models\ContentApproval;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ContentPublicationService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentGovernanceService $governance,
    ) {}

    public function schedule(ContentApproval $approval, Carbon|string $scheduledFor, ?User $actor = null): ContentApproval
    {
        if ((bool) config('belzamora.governance.content_approval.pre_publish_validation', true)) {
            $this->governance->assertPublishable($approval);
        }

        $approval->forceFill([
            'status' => ContentApprovalStatus::Scheduled,
            'reviewed_by_id' => $approval->reviewed_by_id ?? $actor?->id,
            'reviewed_at' => $approval->reviewed_at ?? now(),
            'approved_at' => $approval->approved_at ?? now(),
            'scheduled_for' => $scheduledFor,
            'rejection_reason' => null,
        ])->save();

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

        ContentScheduled::dispatch($approval->fresh(['approvable']));

        return $approval->fresh(['approvable']) ?? $approval;
    }

    public function publish(ContentApproval $approval, ?User $actor = null, string $event = 'content.published'): ContentApproval
    {
        if ((bool) config('belzamora.governance.content_approval.pre_publish_validation', true)) {
            $this->governance->assertPublishable($approval);
        }

        $publishedAt = now();

        $approval->forceFill([
            'status' => ContentApprovalStatus::Published,
            'reviewed_by_id' => $approval->reviewed_by_id ?? $actor?->id,
            'published_at' => $publishedAt,
        ])->save();

        $approvable = $approval->approvable;

        if ($approvable instanceof Model && array_key_exists('published_at', $approvable->getAttributes())) {
            $approvable->forceFill([
                'published_at' => $publishedAt,
            ])->save();
        }

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

        ContentPublished::dispatch($approval->fresh(['approvable']));

        return $approval->fresh(['approvable']) ?? $approval;
    }
}
