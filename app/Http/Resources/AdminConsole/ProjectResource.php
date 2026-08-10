<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\ContentApproval;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\SourceRecord;
use App\Services\ContentGovernanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Project */
class ProjectResource extends JsonResource
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
            : ['No approval record is linked to this project yet.'];

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'href' => route('admin-console.projects.show', $this->resource),
            'publicHref' => route('public.projects', ['category' => $this->category?->name ?? 'All categories']),
            'barangay' => $this->barangay,
            'location' => $this->location,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name ?? 'Uncategorized',
            ],
            'status' => [
                'id' => $this->status?->id,
                'value' => $this->status?->value->value,
                'label' => $this->status?->label ?? 'Status missing',
                'tone' => $this->status?->tone->value ?? 'sourcePending',
            ],
            'partner' => [
                'id' => $this->primaryPartner?->id,
                'name' => $this->primaryPartner?->name,
                'label' => $this->partner_label,
            ],
            'responsibleOffice' => $this->responsible_office,
            'summary' => $this->summary,
            'evidenceSummary' => $this->evidence_summary,
            'impactNote' => $this->impact_note,
            'imageGuidance' => $this->image_guidance,
            'source' => [
                'label' => $this->source_label,
                'url' => $this->source_url,
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
                'publishedAt' => $this->formatDate($this->published_at, 'M j, Y g:i A'),
                'reviewDueAt' => $this->formatDate($this->review_due_at),
                'freshness' => $this->freshness(),
                'readiness' => [
                    'passed' => $issues === [],
                    'issueCount' => count($issues),
                    'issues' => $issues,
                ],
                'approval' => $approval instanceof ContentApproval ? [
                    'id' => $approval->id,
                    'status' => [
                        'value' => $approval->status->value,
                        'label' => $approval->status->label(),
                    ],
                    'revisionNumber' => $approval->revision_number,
                    'changeSummary' => $approval->change_summary,
                    'reviewer' => $approval->reviewer?->name,
                    'submittedAt' => $this->formatDate($approval->submitted_at, 'M j, Y g:i A'),
                    'href' => route('admin-console.content-approvals.show', $approval),
                ] : null,
            ],
            'counts' => [
                'evidences' => (int) ($this->getAttribute('evidences_count') ?? $this->evidences->count()),
                'sources' => (int) ($this->getAttribute('source_records_count') ?? $this->sourceRecords->count()),
                'approvals' => (int) ($this->getAttribute('approval_records_count') ?? $this->approvalRecords->count()),
                'rightsReady' => $this->rightsReadyCount(),
            ],
            'evidences' => $this->whenLoaded('evidences', fn (): array => $this->evidences
                ->map(fn (ProjectEvidence $evidence): array => [
                    'id' => $evidence->id,
                    'title' => $evidence->title,
                    'sourceLabel' => $evidence->source_label,
                    'sourceUrl' => $evidence->source_url,
                    'caption' => $evidence->caption,
                    'altText' => $evidence->alt_text,
                    'rightsNote' => $evidence->rights_note,
                    'mediaPath' => $evidence->media_path,
                    'verifiedAt' => $this->formatDate($evidence->verified_at),
                    'verifier' => $evidence->verifier?->name,
                    'rightsReady' => $this->evidenceRightsReady($evidence),
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
                ->sortByDesc('revision_number')
                ->map(fn (ContentApproval $record): array => [
                    'id' => $record->id,
                    'status' => [
                        'value' => $record->status->value,
                        'label' => $record->status->label(),
                    ],
                    'revisionNumber' => $record->revision_number,
                    'changeSummary' => $record->change_summary,
                    'reviewer' => $record->reviewer?->name,
                    'submittedAt' => $this->formatDate($record->submitted_at, 'M j, Y g:i A'),
                    'href' => route('admin-console.content-approvals.show', $record),
                ])
                ->values()
                ->all()),
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
                'approve' => $request->user()?->can('approve', $this->resource) ?? false,
            ],
        ];
    }

    private function latestApproval(): ?ContentApproval
    {
        /** @var Collection<int, ContentApproval> $approvals */
        $approvals = $this->approvalRecords;

        return $approvals->sortByDesc('revision_number')->first();
    }

    private function latestSource(): ?SourceRecord
    {
        /** @var Collection<int, SourceRecord> $sources */
        $sources = $this->sourceRecords;

        return $sources
            ->sortByDesc(fn (SourceRecord $source): int => $source->verified_at?->getTimestamp() ?? 0)
            ->first();
    }

    private function rightsReadyCount(): int
    {
        if (! $this->relationLoaded('evidences')) {
            return (int) $this->getAttribute('evidences_count');
        }

        return $this->evidences
            ->filter(fn (ProjectEvidence $evidence): bool => $this->evidenceRightsReady($evidence))
            ->count();
    }

    private function evidenceRightsReady(ProjectEvidence $evidence): bool
    {
        return filled($evidence->caption)
            && filled($evidence->alt_text)
            && filled($evidence->rights_note)
            && $evidence->verified_at !== null;
    }

    /**
     * @return array{value: string, label: string, tone: string}
     */
    private function freshness(): array
    {
        if ($this->review_due_at?->isPast()) {
            return ['value' => 'overdue', 'label' => 'Review overdue', 'tone' => 'critical'];
        }

        if ($this->review_due_at?->lte(now()->addDays(
            (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
        ))) {
            return ['value' => 'due_soon', 'label' => 'Review due soon', 'tone' => 'warning'];
        }

        return ['value' => 'current', 'label' => 'Current', 'tone' => 'success'];
    }

    private function formatDate(mixed $value, string $format = 'M j, Y'): ?string
    {
        if (! ($value instanceof \DateTimeInterface)) {
            return null;
        }

        if ($value instanceof \DateTime) {
            return $value->setTimezone(new \DateTimeZone('Asia/Manila'))->format($format);
        }

        return $value->format($format);
    }
}
