<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\ContentApproval;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\SourceRecord;
use App\Services\ContentGovernanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin NewsRecord
 */
class NewsRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $latestSource = $this->latestSource();
        $latestApproval = $this->latestApproval();
        $readiness = $this->readiness($latestApproval);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'href' => route('admin-console.news.show', $this->resource),
            'publicHref' => route('public.news', ['status' => str_replace('_', '-', (string) $this->status?->value->value)]),
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name ?? 'Uncategorized',
            ],
            'status' => [
                'value' => $this->status?->value->value,
                'label' => $this->status?->label ?? 'Status missing',
                'tone' => $this->status?->tone->value ?? 'staleReview',
            ],
            'published' => [
                'label' => $this->published_at_label,
                'at' => $this->formatDateTime($this->published_at),
                'updatedLabel' => $this->public_updated_at_label,
                'updatedAt' => $this->formatDateTime($this->public_updated_at),
            ],
            'summary' => $this->summary,
            'officialUpdate' => $this->official_update,
            'nextStep' => $this->next_step,
            'staleNote' => $this->stale_note,
            'ownerLabel' => $this->owner_label,
            'source' => [
                'label' => $this->source_label,
                'url' => $this->source_url,
                'recordTitle' => $latestSource?->title,
                'recordUrl' => $latestSource?->url,
                'type' => $latestSource?->source_type->label(),
                'tier' => $latestSource?->source_tier,
                'status' => $latestSource?->public_status,
                'verifiedAt' => $this->formatDate($this->source_verified_at ?? $latestSource?->verified_at),
                'nextReviewAt' => $this->formatDate($latestSource?->next_review_at ?? $this->review_due_at),
                'verifier' => $latestSource?->verifier?->name,
            ],
            'governance' => [
                'contentOwner' => $this->contentOwner?->name,
                'approver' => $this->approver?->name,
                'verifiedAt' => $this->formatDate($this->source_verified_at),
                'publishedAt' => $this->formatDateTime($this->published_at),
                'reviewDueAt' => $this->formatDate($this->review_due_at),
                'freshness' => $this->freshness(),
                'readiness' => $readiness,
                'approval' => $latestApproval ? $this->approvalRecord($latestApproval) : null,
            ],
            'counts' => [
                'mediaAssets' => $this->relationLoaded('mediaAssets')
                    ? $this->mediaAssets->count()
                    : (int) $this->getAttribute('media_assets_count'),
                'mediaReady' => $this->mediaReadyCount(),
                'sources' => $this->relationLoaded('sourceRecords')
                    ? $this->sourceRecords->count()
                    : (int) $this->getAttribute('source_records_count'),
                'approvals' => $this->relationLoaded('approvalRecords')
                    ? $this->approvalRecords->count()
                    : (int) $this->getAttribute('approval_records_count'),
            ],
            'mediaAssets' => $this->mediaAssets
                ->map(fn (NewsMediaAsset $asset): array => [
                    'id' => $asset->id,
                    'title' => $asset->title,
                    'mediaType' => $asset->media_type,
                    'mediaPath' => $asset->media_path,
                    'sourceLabel' => $asset->source_label,
                    'sourceUrl' => $asset->source_url,
                    'caption' => $asset->caption,
                    'altText' => $asset->alt_text,
                    'rightsNote' => $asset->rights_note,
                    'verifiedAt' => $this->formatDate($asset->verified_at),
                    'verifier' => $asset->verifier?->name,
                    'rightsReady' => $this->mediaRightsReady($asset),
                ])
                ->values()
                ->all(),
            'sourceRecords' => $this->sourceRecords
                ->map(fn (SourceRecord $source): array => [
                    'id' => $source->id,
                    'title' => $source->title,
                    'url' => $source->url,
                    'type' => $source->source_type->label(),
                    'tier' => $source->source_tier,
                    'status' => $source->public_status,
                    'verifiedAt' => $this->formatDate($source->verified_at),
                    'nextReviewAt' => $this->formatDate($source->next_review_at),
                    'verifier' => $source->verifier?->name,
                ])
                ->values()
                ->all(),
            'approvalRecords' => $this->approvalRecords
                ->map(fn (ContentApproval $approval): array => $this->approvalRecord($approval))
                ->values()
                ->all(),
            'lifecycle' => [
                'trashed' => $this->trashed(),
                'archiveUrl' => route('admin-console.news.archive', $this->resource),
                'restoreUrl' => route('admin-console.news.restore', $this->resource),
                'deleteUrl' => route('admin-console.news.delete', $this->resource),
            ],
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'approve' => $request->user()?->can('approve', $this->resource) ?? false,
                'archive' => $request->user()?->can('delete', $this->resource) ?? false,
                'restore' => $request->user()?->can('restore', $this->resource) ?? false,
                'forceDelete' => $request->user()?->can('forceDelete', $this->resource) ?? false,
            ],
        ];
    }

    private function latestSource(): ?SourceRecord
    {
        return $this->sourceRecords
            ->sortByDesc(fn (SourceRecord $record): int => $record->verified_at?->getTimestamp() ?? 0)
            ->first();
    }

    private function latestApproval(): ?ContentApproval
    {
        return $this->approvalRecords
            ->sortByDesc('revision_number')
            ->first();
    }

    /**
     * @return array{passed: bool, issueCount: int, issues: list<string>}
     */
    private function readiness(?ContentApproval $approval): array
    {
        if (! $approval instanceof ContentApproval) {
            return [
                'passed' => false,
                'issueCount' => 1,
                'issues' => ['No approval record has been created for this news item.'],
            ];
        }

        $issues = app(ContentGovernanceService::class)->blockingIssues($approval);
        $missingMedia = $this->mediaAssets
            ->filter(fn (NewsMediaAsset $asset): bool => ! $this->mediaRightsReady($asset))
            ->count();

        if ($missingMedia > 0) {
            $issues[] = $missingMedia.' media asset(s) still need file, caption, alt text, rights note, and verification.';
        }

        return [
            'passed' => $issues === [],
            'issueCount' => count($issues),
            'issues' => array_values(array_unique($issues)),
        ];
    }

    /**
     * @return array{value: string, label: string, tone: string}
     */
    private function freshness(): array
    {
        if (! $this->review_due_at instanceof \DateTimeInterface) {
            return ['value' => 'missing', 'label' => 'Review date missing', 'tone' => 'critical'];
        }

        if ($this->review_due_at <= now()) {
            return ['value' => 'overdue', 'label' => 'Source review overdue', 'tone' => 'critical'];
        }

        if ($this->review_due_at <= now()->addDays((int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7))) {
            return ['value' => 'soon', 'label' => 'Review due soon', 'tone' => 'warning'];
        }

        return ['value' => 'current', 'label' => 'Current', 'tone' => 'healthy'];
    }

    /**
     * @return array<string, mixed>
     */
    private function approvalRecord(ContentApproval $approval): array
    {
        return [
            'id' => $approval->id,
            'status' => [
                'value' => $approval->status->value,
                'label' => $approval->status->label(),
            ],
            'revisionNumber' => $approval->revision_number,
            'changeSummary' => $approval->change_summary,
            'reviewer' => $approval->reviewer?->name,
            'submittedAt' => $this->formatDateTime($approval->submitted_at),
            'href' => route('admin-console.content-approvals.show', $approval),
        ];
    }

    private function mediaRightsReady(NewsMediaAsset $asset): bool
    {
        return filled($asset->media_path)
            && filled($asset->caption)
            && filled($asset->alt_text)
            && filled($asset->rights_note)
            && $asset->verified_at !== null;
    }

    private function mediaReadyCount(): int
    {
        if (! $this->relationLoaded('mediaAssets')) {
            return 0;
        }

        return $this->mediaAssets
            ->filter(fn (NewsMediaAsset $asset): bool => $this->mediaRightsReady($asset))
            ->count();
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('M j, Y')
            : null;
    }

    private function formatDateTime(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('M j, Y g:i A')
            : null;
    }
}
