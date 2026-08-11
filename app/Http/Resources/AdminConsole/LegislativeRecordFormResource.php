<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\SourceType;
use App\Models\LegislativeRecord;
use App\Models\LegislativeRole;
use App\Models\LegislativeSourceHistory;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\SourceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LegislativeRecordFormResource
{
    public function __construct(
        private readonly ?LegislativeRecord $record,
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
            'title' => $record instanceof LegislativeRecord ? 'Edit '.$record->public_title : 'Create legislative record',
            'action' => $record instanceof LegislativeRecord
                ? route('admin-console.legislation.update', $record)
                : route('admin-console.legislation.store'),
            'method' => $record instanceof LegislativeRecord ? 'put' : 'post',
            'backHref' => $record instanceof LegislativeRecord
                ? route('admin-console.legislation.show', $record)
                : route('admin-console.legislation.index'),
            'indexHref' => route('admin-console.legislation.index'),
            'recordHref' => $record instanceof LegislativeRecord ? route('admin-console.legislation.show', $record) : null,
            'values' => $this->values($record, $source),
            'options' => $this->options(),
            'governanceNote' => 'Legislative records must use plain-language explanations, official-source links, role/status/topic taxonomy, and review dates before governed publication.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function values(?LegislativeRecord $record, ?SourceRecord $source): array
    {
        return [
            'legislative_role_id' => $record?->legislative_role_id ? (string) $record->legislative_role_id : '',
            'legislative_status_id' => $record?->legislative_status_id ? (string) $record->legislative_status_id : '',
            'legislative_topic_id' => $record?->legislative_topic_id ? (string) $record->legislative_topic_id : '',
            'bill_number' => $record?->bill_number ?? '',
            'slug' => $record?->slug ?? '',
            'official_title' => $record?->official_title ?? '',
            'public_title' => $record?->public_title ?? '',
            'congress' => $record?->congress ?? '20th Congress',
            'committee' => $record?->committee ?? '',
            'filed_at_label' => $record?->filed_at_label ?? 'Source-pending date',
            'filed_at' => $this->dateValue($record?->filed_at),
            'verified_at' => $this->dateValue($record?->verified_at ?? $source?->verified_at),
            'plain_language_summary' => $record?->plain_language_summary ?? '',
            'why_it_matters' => $record?->why_it_matters ?? '',
            'san_juan_relevance' => $record?->san_juan_relevance ?? '',
            'official_source_label' => $record?->official_source_label ?? $source?->title ?? '',
            'official_source_url' => $record?->official_source_url ?? $source?->url ?? '',
            'published_at' => $this->dateTimeValue($record?->published_at),
            'review_due_at' => $this->dateValue($record?->review_due_at ?? $source?->next_review_at),
            'sort_order' => (int) ($record?->sort_order ?? 0),
            'source_record' => [
                'title' => $source?->title ?? $record?->official_source_label ?? '',
                'url' => $source?->url ?? $record?->official_source_url ?? '',
                'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
                'source_tier' => $source?->source_tier ?? 'tier_1',
                'public_status' => $source?->public_status ?? 'published',
                'description' => $source?->description ?? '',
                'verified_at' => $this->dateValue($source?->verified_at ?? $record?->verified_at),
                'next_review_at' => $this->dateValue($source?->next_review_at ?? $record?->review_due_at),
            ],
            'source_histories' => $this->sourceHistories($record),
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
            'roles' => LegislativeRole::query()
                ->active()
                ->ordered()
                ->get(['id', 'label', 'value'])
                ->map(fn (LegislativeRole $role): array => [
                    'value' => (string) $role->id,
                    'label' => $role->label,
                    'description' => $role->value->label(),
                ])
                ->values()
                ->all(),
            'statuses' => LegislativeStatus::query()
                ->active()
                ->ordered()
                ->get(['id', 'label', 'tone'])
                ->map(fn (LegislativeStatus $status): array => [
                    'value' => (string) $status->id,
                    'label' => $status->label,
                    'description' => $status->tone->label(),
                ])
                ->values()
                ->all(),
            'topics' => LegislativeTopic::query()
                ->active()
                ->ordered()
                ->get(['id', 'name', 'description'])
                ->map(fn (LegislativeTopic $topic): array => [
                    'value' => (string) $topic->id,
                    'label' => $topic->name,
                    'description' => $topic->description,
                ])
                ->values()
                ->all(),
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
    private function sourceHistories(?LegislativeRecord $record): array
    {
        if (! $record instanceof LegislativeRecord || ! $record->relationLoaded('sourceHistories')) {
            return [[
                'source_label' => '',
                'source_url' => '',
                'status_snapshot' => '',
                'checked_at' => '',
                'notes' => '',
            ]];
        }

        return $record->sourceHistories
            ->map(fn (LegislativeSourceHistory $history): array => [
                'source_label' => $history->source_label,
                'source_url' => $history->source_url,
                'status_snapshot' => $history->status_snapshot,
                'checked_at' => $this->dateValue($history->checked_at) ?? '',
                'notes' => $history->notes ?? '',
            ])
            ->values()
            ->all() ?: [[
                'source_label' => '',
                'source_url' => '',
                'status_snapshot' => '',
                'checked_at' => '',
                'notes' => '',
            ]];
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
