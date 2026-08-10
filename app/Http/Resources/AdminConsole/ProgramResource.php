<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\ContentApproval;
use App\Models\Program;
use App\Models\ProgramRequirement;
use App\Models\ProgramUpdate;
use App\Models\SourceRecord;
use App\Services\ContentGovernanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Program */
class ProgramResource extends JsonResource
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
            : ['No approval record is linked to this program yet.'];

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'href' => route('admin-console.programs.show', $this->resource),
            'publicHref' => $this->href,
            'sector' => $this->sector,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name ?? 'Uncategorized',
            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'summary' => $this->summary,
            'description' => $this->description,
            'responsibleOffice' => [
                'type' => $this->responsible_office_type->value,
                'typeLabel' => $this->responsible_office_type->label(),
                'label' => $this->responsible_office_label,
            ],
            'partnerAgency' => $this->partner_agency,
            'beneficiaryGroup' => $this->beneficiary_group,
            'applicationPeriod' => $this->application_period,
            'benefit' => $this->benefit,
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
                'requirements' => (int) ($this->getAttribute('requirements_count') ?? $this->requirements->count()),
                'updates' => (int) ($this->getAttribute('updates_count') ?? $this->updates->count()),
                'sources' => (int) ($this->getAttribute('source_records_count') ?? $this->sourceRecords->count()),
                'approvals' => (int) ($this->getAttribute('approval_records_count') ?? $this->approvalRecords->count()),
            ],
            'requirements' => $this->whenLoaded('requirements', fn (): array => $this->requirements
                ->map(fn (ProgramRequirement $requirement): array => [
                    'id' => $requirement->id,
                    'groupTitle' => $requirement->group_title,
                    'itemText' => $requirement->item_text,
                    'note' => $requirement->note,
                ])
                ->values()
                ->all()),
            'updates' => $this->whenLoaded('updates', fn (): array => $this->updates
                ->sortByDesc('published_at')
                ->map(fn (ProgramUpdate $update): array => [
                    'id' => $update->id,
                    'title' => $update->title,
                    'summary' => $update->summary,
                    'status' => [
                        'value' => $update->status->value,
                        'label' => $update->status->label(),
                    ],
                    'sourceLabel' => $update->source_label,
                    'sourceUrl' => $update->source_url,
                    'verifiedAt' => $this->formatDate($update->verified_at),
                    'publishedAt' => $this->formatDate($update->published_at, 'M j, Y g:i A'),
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
