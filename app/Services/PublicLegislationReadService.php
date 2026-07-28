<?php

namespace App\Services;

use App\Models\LegislativeRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicLegislationReadService
{
    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     legislativeItems: array<int, array<string, mixed>>,
     *     roleOptions: array<int, string>,
     *     statusOptions: array<int, string>,
     *     topicOptions: array<int, string>,
     *     initialFilters: array{role: string, status: string, topic: string},
     *     verifiedAt: string
     * }
     */
    public function indexProps(Request $request): array
    {
        $base = Cache::remember(
            PublicCacheInvalidator::LEGISLATION_INDEX,
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            fn (): array => $this->baseIndexPayload(),
        );

        return [
            ...$base,
            'initialFilters' => [
                'role' => $this->roleFromQuery($request),
                'status' => $request->query('status', 'All statuses'),
                'topic' => $request->query('topic', 'All topics'),
            ],
        ];
    }

    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     legislativeItems: array<int, array<string, mixed>>,
     *     roleOptions: array<int, string>,
     *     statusOptions: array<int, string>,
     *     topicOptions: array<int, string>,
     *     verifiedAt: string
     * }
     */
    private function baseIndexPayload(): array
    {
        $items = LegislativeRecord::query()
            ->published()
            ->with(['role', 'status', 'topic', 'sourceHistories'])
            ->ordered()
            ->get()
            ->map(fn (LegislativeRecord $record): array => $this->legislativeItem($record));

        return [
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'Legislation'],
            ],
            'legislativeItems' => $items->values()->all(),
            'roleOptions' => $items->pluck('roleLabel')->unique()->values()->all(),
            'statusOptions' => $items->pluck('status')->unique()->values()->all(),
            'topicOptions' => $items->pluck('topic')->unique()->values()->all(),
            'verifiedAt' => $this->latestVerifiedAt($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function legislativeItem(LegislativeRecord $record): array
    {
        return [
            'billNumber' => $record->bill_number,
            'officialTitle' => $record->official_title,
            'publicTitle' => $record->public_title,
            'congress' => $record->congress,
            'role' => $record->role->value->value,
            'roleLabel' => $record->role->label,
            'status' => $record->status->label,
            'statusTone' => $record->status->tone->value,
            'topic' => $record->topic->name,
            'committee' => $record->committee,
            'filedAt' => $record->filed_at_label,
            'verifiedAt' => $this->formatDate($record->verified_at),
            'reviewDueAt' => $this->formatDate($record->review_due_at),
            'trustStatus' => $this->trustStatus($record->review_due_at),
            'trustStatusLabel' => $this->trustStatusLabel($record->review_due_at),
            'plainLanguageSummary' => $record->plain_language_summary,
            'whyItMatters' => $record->why_it_matters,
            'sanJuanRelevance' => $record->san_juan_relevance,
            'officialSourceLabel' => $record->official_source_label,
            'officialSourceUrl' => $record->official_source_url,
        ];
    }

    private function roleFromQuery(Request $request): string
    {
        return match ($request->query('role')) {
            'principal' => 'Principal Author',
            'co-author' => 'Co-Author',
            'sponsor' => 'Sponsor',
            default => 'All roles',
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function latestVerifiedAt(Collection $items): string
    {
        $latest = $items
            ->pluck('verifiedAt')
            ->filter()
            ->sort()
            ->last();

        return is_string($latest) ? $latest : 'Source review pending';
    }

    private function formatDate(mixed $date): string
    {
        if (! $date instanceof \DateTimeInterface) {
            return 'Source review pending';
        }

        return \DateTimeImmutable::createFromInterface($date)
            ->setTimezone(new \DateTimeZone('Asia/Manila'))
            ->format('F j, Y');
    }

    private function trustStatus(mixed $reviewDueAt): string
    {
        if (! $reviewDueAt instanceof \DateTimeInterface) {
            return 'source-review-pending';
        }

        $date = \DateTimeImmutable::createFromInterface($reviewDueAt);

        if ($date <= now()) {
            return 'stale-review';
        }

        if ($date <= now()->addDays((int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7))) {
            return 'review-soon';
        }

        return 'current';
    }

    private function trustStatusLabel(mixed $reviewDueAt): string
    {
        return match ($this->trustStatus($reviewDueAt)) {
            'stale-review' => 'Needs source review before reuse',
            'review-soon' => 'Review due soon',
            'source-review-pending' => 'Source review pending',
            default => 'Current verified information',
        };
    }
}
