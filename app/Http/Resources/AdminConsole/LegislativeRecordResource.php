<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\ContentApproval;
use App\Models\LegislativeRecord;
use App\Models\LegislativeSourceHistory;
use App\Models\SourceRecord;
use App\Services\ContentGovernanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/** @mixin LegislativeRecord */
class LegislativeRecordResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $approval = $this->latestApproval();
        $source = $this->latestSource();
        $issues = $approval instanceof ContentApproval
            ? app(ContentGovernanceService::class)->blockingIssues($approval)
            : ['No approval record is linked to this legislative record yet.'];

        return [
            'id' => $this->id,
            'title' => $this->public_title,
            'billNumber' => $this->bill_number,
            'slug' => $this->slug,
            'href' => route('admin-console.legislation.show', $this->resource),
            'publicHref' => route('public.legislation', ['topic' => $this->topic?->slug]),
            'officialTitle' => $this->official_title,
            'publicTitle' => $this->public_title,
            'congress' => $this->congress,
            'committee' => $this->committee,
            'filedAtLabel' => $this->filed_at_label,
            'filedAt' => $this->formatDate($this->filed_at),
            'role' => [
                'id' => $this->role?->id,
                'value' => $this->role?->value->value,
                'label' => $this->role?->label ?? 'Role missing',
            ],
            'status' => [
                'id' => $this->status?->id,
                'label' => $this->status?->label ?? 'Status missing',
                'tone' => $this->status?->tone->value ?? 'pending',
            ],
            'topic' => [
                'id' => $this->topic?->id,
                'name' => $this->topic?->name ?? 'Topic missing',
            ],
            'plainLanguageSummary' => $this->plain_language_summary,
            'whyItMatters' => $this->why_it_matters,
            'sanJuanRelevance' => $this->san_juan_relevance,
            'source' => [
                'label' => $this->official_source_label,
                'url' => $this->official_source_url,
                'recordTitle' => $source?->title,
                'recordUrl' => $source?->url,
                'type' => $source?->source_type->label(),
                'tier' => $source?->source_tier,
                'status' => $source?->public_status,
                'verifiedAt' => $this->formatDate($source?->verified_at) ?? $this->formatDate($this->verified_at),
                'nextReviewAt' => $this->formatDate($source?->next_review_at),
                'verifier' => $source?->verifier?->name,
            ],
            'governance' => [
                'contentOwner' => $this->contentOwner?->name,
                'approver' => $this->approver?->name,
                'verifiedAt' => $this->formatDate($this->verified_at),
                'publishedAt' => $this->formatDateTime($this->published_at),
                'reviewDueAt' => $this->formatDate($this->review_due_at),
                'freshness' => $this->freshness(),
                'readiness' => [
                    'passed' => $issues === [],
                    'issueCount' => count($issues),
                    'issues' => $issues,
                ],
                'approval' => $approval instanceof ContentApproval ? $this->approvalRecord($approval) : null,
            ],
            'counts' => [
                'sourceHistories' => $this->relationLoaded('sourceHistories')
                    ? $this->sourceHistories->count()
                    : (int) $this->getAttribute('source_histories_count'),
                'sources' => $this->relationLoaded('sourceRecords')
                    ? $this->sourceRecords->count()
                    : (int) $this->getAttribute('source_records_count'),
                'approvals' => $this->relationLoaded('approvalRecords')
                    ? $this->approvalRecords->count()
                    : (int) $this->getAttribute('approval_records_count'),
            ],
            'sourceHistories' => $this->whenLoaded('sourceHistories', fn (): array => $this->sourceHistories
                ->map(fn (LegislativeSourceHistory $history): array => [
                    'id' => $history->id,
                    'sourceLabel' => $history->source_label,
                    'sourceUrl' => $history->source_url,
                    'statusSnapshot' => $history->status_snapshot,
                    'checkedAt' => $this->formatDate($history->checked_at),
                    'checker' => $history->checker?->name,
                    'notes' => $history->notes,
                ])
                ->values()
                ->all()),
            'sourceRecords' => $this->whenLoaded('sourceRecords', fn (): array => $this->sourceRecords
                ->map(fn (SourceRecord $record): array => [
                    'id' => $record->id,
                    'title' => $record->title,
                    'url' => $record->url,
                    'type' => $record->source_type->label(),
                    'tier' => $record->source_tier,
                    'status' => $record->public_status,
                    'verifiedAt' => $this->formatDate($record->verified_at),
                    'nextReviewAt' => $this->formatDate($record->next_review_at),
                    'verifier' => $record->verifier?->name,
                ])
                ->values()
                ->all()),
            'approvalRecords' => $this->whenLoaded('approvalRecords', fn (): array => $this->approvalRecords
                ->map(fn (ContentApproval $record): array => $this->approvalRecord($record))
                ->values()
                ->all()),
            'lifecycle' => [
                'trashed' => $this->trashed(),
                'archiveUrl' => route('admin-console.legislation.archive', $this->resource),
                'restoreUrl' => route('admin-console.legislation.restore', $this->resource),
                'deleteUrl' => route('admin-console.legislation.delete', $this->resource),
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
