<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\ContentApproval;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use App\Services\ContentGovernanceService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin PublicPage
 */
class PublicPageResource extends JsonResource
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
            'component' => $this->component,
            'href' => route('admin-console.public-pages.show', $this->resource),
            'publicHref' => $this->publicHref(),
            'routeName' => data_get($this->metadata, 'route_name', $this->routeNameForSlug()),
            'pageType' => data_get($this->metadata, 'page_type', $this->pageTypeForSlug()),
            'ownerLabel' => $this->owner_label,
            'source' => [
                'label' => $this->source_label,
                'url' => $this->source_url,
                'recordTitle' => $latestSource?->title,
                'recordUrl' => $latestSource?->url,
                'type' => $latestSource?->source_type->label(),
                'tier' => $latestSource?->source_tier,
                'status' => $latestSource?->public_status,
                'verifiedAt' => $this->formatDate($this->verified_at ?? $latestSource?->verified_at),
                'nextReviewAt' => $this->formatDate($latestSource?->next_review_at ?? $this->review_due_at),
                'verifier' => $latestSource?->verifier?->name,
            ],
            'seo' => [
                'title' => data_get($this->metadata, 'seo.title'),
                'description' => data_get($this->metadata, 'seo.description'),
                'canonicalUrl' => data_get($this->metadata, 'seo.canonical_url'),
                'ogImage' => data_get($this->metadata, 'seo.og_image'),
            ],
            'governance' => [
                'contentOwner' => $this->contentOwner?->name,
                'approver' => $this->approver?->name,
                'verifiedAt' => $this->formatDate($this->verified_at),
                'publishedAt' => $this->formatDateTime($this->published_at),
                'reviewDueAt' => $this->formatDate($this->review_due_at),
                'freshness' => $this->freshness(),
                'readiness' => $readiness,
                'approval' => $latestApproval ? $this->approvalRecord($latestApproval) : null,
            ],
            'counts' => [
                'sources' => $this->relationLoaded('sourceRecords')
                    ? $this->sourceRecords->count()
                    : (int) $this->getAttribute('source_records_count'),
                'approvals' => $this->relationLoaded('approvalRecords')
                    ? $this->approvalRecords->count()
                    : (int) $this->getAttribute('approval_records_count'),
                'propsKeys' => count($this->props ?? []),
            ],
            'propsPreview' => array_slice(array_keys($this->props ?? []), 0, 8),
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
                'archiveUrl' => route('admin-console.public-pages.archive', $this->resource),
                'restoreUrl' => route('admin-console.public-pages.restore', $this->resource),
                'deleteUrl' => route('admin-console.public-pages.delete', $this->resource),
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
                'issues' => ['No approval record has been created for this public page.'],
            ];
        }

        $issues = app(ContentGovernanceService::class)->blockingIssues($approval);

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

    private function publicHref(): string
    {
        return $this->slug === 'home'
            ? route('home')
            : url('/'.$this->slug);
    }

    private function routeNameForSlug(): string
    {
        return match ($this->slug) {
            'home' => 'home',
            'about' => 'public.about',
            'contact' => 'public.contact',
            'privacy' => 'public.privacy',
            'accessibility' => 'public.accessibility',
            'disclaimer' => 'public.disclaimer',
            default => 'public.'.$this->slug,
        };
    }

    private function pageTypeForSlug(): string
    {
        return match ($this->slug) {
            'home' => 'landing',
            'about' => 'profile',
            'contact' => 'contact',
            'privacy', 'accessibility' => 'policy',
            'disclaimer' => 'legal',
            default => 'utility',
        };
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
