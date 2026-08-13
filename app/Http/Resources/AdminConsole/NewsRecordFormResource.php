<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\SourceType;
use App\Models\NewsCategory;
use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\NewsStatus;
use App\Models\SourceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NewsRecordFormResource
{
    public function __construct(
        private readonly ?NewsRecord $record,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $record = $this->record;
        $source = $record?->sourceRecords->sortByDesc(
            fn (SourceRecord $sourceRecord): int => $sourceRecord->verified_at?->getTimestamp() ?? 0,
        )->first();

        return [
            'mode' => $this->mode,
            'title' => $record instanceof NewsRecord ? 'Edit '.$record->title : 'Create news/media record',
            'action' => $record instanceof NewsRecord
                ? route('admin-console.news.update', $record)
                : route('admin-console.news.store'),
            'method' => $record instanceof NewsRecord ? 'put' : 'post',
            'backHref' => $record instanceof NewsRecord
                ? route('admin-console.news.show', $record)
                : route('admin-console.news.index'),
            'indexHref' => route('admin-console.news.index'),
            'recordHref' => $record instanceof NewsRecord ? route('admin-console.news.show', $record) : null,
            'values' => $this->values($record, $source),
            'options' => $this->options(),
            'governanceNote' => 'News and media records must include the official source, owner, review status, public update date, stale-content note, and media-rights details before publication review.',
            'mediaUploadFallbackNote' => 'This editor manages media details. Upload approved media files from the news media page after saving the record.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function values(?NewsRecord $record, ?SourceRecord $source): array
    {
        return [
            'news_category_id' => $record?->news_category_id ? (string) $record->news_category_id : '',
            'news_status_id' => $record?->news_status_id ? (string) $record->news_status_id : '',
            'title' => $record?->title ?? '',
            'slug' => $record?->slug ?? '',
            'published_at_label' => $record?->published_at_label ?? '',
            'published_at' => $this->dateTimeValue($record?->published_at),
            'public_updated_at_label' => $record?->public_updated_at_label ?? '',
            'public_updated_at' => $this->dateTimeValue($record?->public_updated_at),
            'source_label' => $record?->source_label ?? $source?->title ?? '',
            'source_url' => $record?->source_url ?? $source?->url ?? '',
            'summary' => $record?->summary ?? '',
            'official_update' => $record?->official_update ?? '',
            'next_step' => $record?->next_step ?? '',
            'stale_note' => $record?->stale_note ?? '',
            'owner_label' => $record?->owner_label ?? '',
            'source_verified_at' => $this->dateValue($record?->source_verified_at ?? $source?->verified_at),
            'review_due_at' => $this->dateValue($record?->review_due_at ?? $source?->next_review_at),
            'sort_order' => (int) ($record?->sort_order ?? 0),
            'source_record' => [
                'title' => $source?->title ?? $record?->source_label ?? '',
                'url' => $source?->url ?? $record?->source_url ?? '',
                'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
                'source_tier' => $source?->source_tier ?? 'tier_1',
                'public_status' => $source?->public_status ?? 'published',
                'description' => $source?->description ?? '',
                'verified_at' => $this->dateValue($source?->verified_at ?? $record?->source_verified_at),
                'next_review_at' => $this->dateValue($source?->next_review_at ?? $record?->review_due_at),
            ],
            'media_assets' => $this->mediaAssets($record),
            'approval' => [
                'submit_for_review' => false,
                'change_summary' => '',
                'is_fast_track' => false,
                'valid_until' => '',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
            'categories' => NewsCategory::query()
                ->active()
                ->ordered()
                ->get(['id', 'name'])
                ->map(fn (NewsCategory $category): array => [
                    'value' => (string) $category->id,
                    'label' => $category->name,
                ])
                ->values()
                ->all(),
            'statuses' => NewsStatus::query()
                ->active()
                ->ordered()
                ->get(['id', 'label', 'value'])
                ->map(fn (NewsStatus $status): array => [
                    'value' => (string) $status->id,
                    'label' => $status->label,
                    'description' => $status->value->label(),
                ])
                ->values()
                ->all(),
            'mediaTypes' => [
                ['value' => 'image', 'label' => 'Image'],
                ['value' => 'video', 'label' => 'Video'],
                ['value' => 'document', 'label' => 'Document'],
                ['value' => 'link', 'label' => 'External link'],
            ],
            'sourceTypes' => collect(SourceType::cases())
                ->map(fn (SourceType $type): array => [
                    'value' => $type->value,
                    'label' => $type->label(),
                ])
                ->values()
                ->all(),
            'sourceTiers' => [
                ['value' => 'tier_1', 'label' => 'Tier 1 - official source'],
                ['value' => 'tier_2', 'label' => 'Tier 2 - verified partner source'],
                ['value' => 'tier_3', 'label' => 'Tier 3 - historical reference'],
            ],
            'sourcePublicStatuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'archived', 'label' => 'Archived'],
                ['value' => 'needs_review', 'label' => 'Needs review'],
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mediaAssets(?NewsRecord $record): array
    {
        if (! $record instanceof NewsRecord || ! $record->relationLoaded('mediaAssets')) {
            return [$this->defaultMediaAsset()];
        }

        return $record->mediaAssets
            ->map(fn (NewsMediaAsset $asset): array => [
                'title' => $asset->title,
                'media_type' => $asset->media_type,
                'media_path' => $asset->media_path ?? '',
                'source_label' => $asset->source_label,
                'source_url' => $asset->source_url,
                'caption' => $asset->caption ?? '',
                'alt_text' => $asset->alt_text ?? '',
                'rights_note' => $asset->rights_note ?? '',
                'verified_at' => $this->dateValue($asset->verified_at) ?? '',
            ])
            ->values()
            ->all() ?: [$this->defaultMediaAsset()];
    }

    /**
     * @return array<string, string>
     */
    private function defaultMediaAsset(): array
    {
        return [
            'title' => '',
            'media_type' => 'image',
            'media_path' => '',
            'source_label' => '',
            'source_url' => '',
            'caption' => '',
            'alt_text' => '',
            'rights_note' => '',
            'verified_at' => '',
        ];
    }

    private function dateValue(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->toDateString()
            : null;
    }

    private function dateTimeValue(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('Y-m-d\TH:i')
            : null;
    }
}
