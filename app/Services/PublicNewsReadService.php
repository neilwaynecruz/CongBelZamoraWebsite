<?php

namespace App\Services;

use App\Models\NewsRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicNewsReadService
{
    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     newsItems: array<int, array<string, mixed>>,
     *     categoryOptions: array<int, string>,
     *     statusOptions: array<int, string>,
     *     yearOptions: array<int, string>,
     *     initialFilters: array{category: string, status: string, year: string},
     *     verifiedAt: string
     * }
     */
    public function indexProps(Request $request): array
    {
        $base = Cache::remember(
            PublicCacheInvalidator::NEWS_INDEX,
            (int) config('belzamora.cache.public_ttl_seconds', 60),
            fn (): array => $this->baseIndexPayload(),
        );

        return [
            ...$base,
            'initialFilters' => [
                'category' => $request->query('category', 'All categories'),
                'status' => $this->statusFromQuery($request),
                'year' => $request->query('year', 'All years'),
            ],
        ];
    }

    /**
     * @return array{
     *     breadcrumbs: array<int, array<string, string>>,
     *     newsItems: array<int, array<string, mixed>>,
     *     categoryOptions: array<int, string>,
     *     statusOptions: array<int, string>,
     *     yearOptions: array<int, string>,
     *     verifiedAt: string
     * }
     */
    private function baseIndexPayload(): array
    {
        $items = NewsRecord::query()
            ->published()
            ->with(['category', 'status', 'mediaAssets'])
            ->ordered()
            ->get()
            ->map(fn (NewsRecord $record): array => $this->newsItem($record));

        return [
            'breadcrumbs' => [
                ['title' => 'Home', 'href' => '/'],
                ['title' => 'News'],
            ],
            'newsItems' => $items->values()->all(),
            'categoryOptions' => $items->pluck('category')->unique()->values()->all(),
            'statusOptions' => $items->pluck('statusLabel')->unique()->values()->all(),
            'yearOptions' => $items
                ->pluck('publishedAt')
                ->map(fn (string $date): string => str($date)->afterLast(' ')->toString())
                ->unique()
                ->values()
                ->all(),
            'verifiedAt' => $this->latestVerifiedAt($items),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsItem(NewsRecord $record): array
    {
        return [
            'title' => $record->title,
            'slug' => $record->slug,
            'category' => $record->category->name,
            'status' => $record->status->value->value,
            'statusLabel' => $record->status->label,
            'statusTone' => $record->status->tone->value,
            'publishedAt' => $record->published_at_label,
            'updatedAt' => $record->public_updated_at_label,
            'sourceLabel' => $record->source_label,
            'sourceUrl' => $record->source_url,
            'summary' => $record->summary,
            'officialUpdate' => $record->official_update,
            'nextStep' => $record->next_step,
            'staleNote' => $record->stale_note,
            'owner' => $record->owner_label,
            'reviewDueAt' => $this->formatDate($record->review_due_at),
            'trustStatus' => $this->trustStatus($record->review_due_at),
            'trustStatusLabel' => $this->trustStatusLabel($record->review_due_at),
        ];
    }

    private function statusFromQuery(Request $request): string
    {
        return match ($request->query('status')) {
            'current' => 'Current advisory',
            'archived' => 'Archived official update',
            'stale-review' => 'Needs review before reuse',
            default => 'All statuses',
        };
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function latestVerifiedAt(Collection $items): string
    {
        $latest = $items
            ->pluck('updatedAt')
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
