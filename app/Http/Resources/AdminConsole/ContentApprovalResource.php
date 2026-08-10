<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\User;
use App\Services\ContentGovernanceService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ContentApproval */
class ContentApprovalResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $governance = app(ContentGovernanceService::class);
        $issues = $governance->blockingIssues($this->resource);
        $policy = $this->approvable instanceof Model ? $governance->policyFor($this->approvable) : null;

        return [
            'id' => $this->id,
            'recordType' => $this->approvable instanceof Model
                ? str(class_basename($this->approvable))->headline()->toString()
                : 'Unavailable record',
            'recordTitle' => $this->recordTitle(),
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'revisionNumber' => $this->revision_number,
            'changeSummary' => $this->change_summary,
            'reviewNotes' => $this->review_notes,
            'rejectionReason' => $this->rejection_reason,
            'archiveReason' => $this->archive_reason,
            'submitter' => $this->submitter?->name,
            'submitterId' => $this->submitted_by_id,
            'reviewer' => $this->reviewer?->name,
            'reviewerId' => $this->reviewed_by_id,
            'submittedAt' => $this->submitted_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'reviewedAt' => $this->reviewed_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'approvedAt' => $this->approved_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'scheduledFor' => $this->scheduled_for?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'publishedAt' => $this->published_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'unpublishedAt' => $this->unpublished_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
            'validUntil' => $this->valid_until?->timezone('Asia/Manila')->format('M j, Y'),
            'isFastTrack' => $this->is_fast_track,
            'reviewDueAt' => $this->modelDate('review_due_at', 'M j, Y'),
            'verifiedAt' => $this->modelDate('verified_at', 'M j, Y')
                ?? $this->modelDate('source_verified_at', 'M j, Y'),
            'contentOwner' => $this->contentOwnerName(),
            'source' => [
                'label' => $this->modelString('source_label') ?? $this->modelString('official_source_label'),
                'url' => $this->modelString('source_url') ?? $this->modelString('official_source_url'),
            ],
            'freshness' => $this->freshness(),
            'urgency' => $this->urgency(),
            'readiness' => [
                'passed' => $issues === [],
                'issueCount' => count($issues),
                'issues' => $issues,
            ],
            'policy' => is_array($policy) ? [
                'label' => (string) data_get($policy, 'label', $this->recordTitle()),
                'reviewCadenceDays' => (int) data_get($policy, 'review_cadence_days', 0),
                'sourcePolicy' => (string) data_get($policy, 'source_policy', ''),
                'requiredFields' => array_values(array_filter(
                    (array) data_get($policy, 'required_publish_fields', []),
                    'is_string',
                )),
            ] : null,
            'href' => route('admin-console.content-approvals.show', $this->resource),
        ];
    }

    /** @return array{value: string, label: string, tone: string} */
    private function freshness(): array
    {
        $reviewDueAt = $this->approvable instanceof Model
            ? $this->approvable->getAttribute('review_due_at')
            : null;

        if ($this->valid_until?->isPast()) {
            return ['value' => 'expired', 'label' => 'Validity expired', 'tone' => 'critical'];
        }

        if ($reviewDueAt instanceof \DateTimeInterface && now()->greaterThanOrEqualTo($reviewDueAt)) {
            return ['value' => 'overdue', 'label' => 'Review overdue', 'tone' => 'critical'];
        }

        $warningDays = (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7);

        if ($reviewDueAt instanceof \DateTimeInterface && now()->addDays($warningDays)->greaterThanOrEqualTo($reviewDueAt)) {
            return ['value' => 'due_soon', 'label' => 'Review due soon', 'tone' => 'warning'];
        }

        return ['value' => 'current', 'label' => 'Current', 'tone' => 'success'];
    }

    /** @return array{value: string, label: string, tone: string} */
    private function urgency(): array
    {
        $freshness = $this->freshness();

        if ($freshness['tone'] === 'critical') {
            return ['value' => 'critical', 'label' => 'Immediate review', 'tone' => 'critical'];
        }

        if ($this->is_fast_track) {
            return ['value' => 'fast_track', 'label' => 'Fast-track', 'tone' => 'warning'];
        }

        if ($this->status === ContentApprovalStatus::ForReview && $this->reviewed_by_id === null) {
            return ['value' => 'unassigned', 'label' => 'Needs owner', 'tone' => 'warning'];
        }

        if ($freshness['tone'] === 'warning') {
            return ['value' => 'due_soon', 'label' => 'Review soon', 'tone' => 'warning'];
        }

        return ['value' => 'routine', 'label' => 'Routine', 'tone' => 'neutral'];
    }

    private function modelString(string $attribute): ?string
    {
        if (! $this->approvable instanceof Model) {
            return null;
        }

        $value = $this->approvable->getAttribute($attribute);

        return is_string($value) && $value !== '' ? $value : null;
    }

    private function modelDate(string $attribute, string $format): ?string
    {
        if (! $this->approvable instanceof Model) {
            return null;
        }

        $value = $this->approvable->getAttribute($attribute);

        return $value instanceof \DateTimeInterface
            ? $value->setTimezone(new \DateTimeZone('Asia/Manila'))->format($format)
            : null;
    }

    private function contentOwnerName(): ?string
    {
        if (! $this->approvable instanceof Model || ! method_exists($this->approvable, 'contentOwner')) {
            return null;
        }

        $this->approvable->loadMissing('contentOwner');

        $owner = $this->approvable->getRelation('contentOwner');

        return $owner instanceof User ? $owner->name : null;
    }

    private function recordTitle(): string
    {
        if (! $this->approvable instanceof Model) {
            return 'Record unavailable';
        }

        foreach (['title', 'public_title', 'official_title', 'name', 'label', 'bill_number'] as $attribute) {
            $value = $this->approvable->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return class_basename($this->approvable).' #'.$this->approvable->getKey();
    }
}
