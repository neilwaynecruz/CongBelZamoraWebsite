<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\SourceType;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SourceRecordFormResource
{
    public function __construct(
        private readonly ?SourceRecord $sourceRecord,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'mode' => $this->mode,
            'title' => $this->sourceRecord instanceof SourceRecord
                ? 'Edit '.$this->sourceRecord->title
                : 'Create official source',
            'action' => $this->sourceRecord instanceof SourceRecord
                ? route('admin-console.source-records.update', $this->sourceRecord)
                : route('admin-console.source-records.store'),
            'method' => $this->sourceRecord instanceof SourceRecord ? 'put' : 'post',
            'backHref' => $this->sourceRecord instanceof SourceRecord
                ? route('admin-console.source-records.show', $this->sourceRecord)
                : route('admin-console.source-records.index'),
            'indexHref' => route('admin-console.source-records.index'),
            'values' => $this->values(),
            'options' => $this->options(),
            'governanceNote' => 'Official sources document the proof staff rely on for public claims, service guidance, and content review. Keep review dates current and avoid private resident documents.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function values(): array
    {
        $source = $this->sourceRecord;

        return [
            'title' => $source?->title ?? '',
            'url' => $source?->url ?? '',
            'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
            'source_tier' => $source?->source_tier ?? 'tier_1',
            'public_status' => $source?->public_status ?? 'published',
            'description' => $source?->description ?? '',
            'content_owner_id' => $source?->content_owner_id,
            'verified_by_id' => $source?->verified_by_id,
            'verified_at' => $this->dateValue($source?->verified_at),
            'last_reviewed_at' => $this->dateValue($source?->last_reviewed_at),
            'next_review_at' => $this->dateValue($source?->next_review_at),
            'effective_at' => $this->dateValue($source?->effective_at),
            'expires_at' => $this->dateValue($source?->expires_at),
            'internal_notes' => $source?->internal_notes ?? '',
            'metadata_json' => $this->json($source?->metadata ?? []),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function options(): array
    {
        return [
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
                ['value' => 'tier_3', 'label' => 'Tier 3 - historical or discovery source'],
            ],
            'publicStatuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
                ['value' => 'archived', 'label' => 'Archived'],
                ['value' => 'needs_review', 'label' => 'Needs review'],
            ],
            'staff' => User::query()
                ->whereNotNull('admin_role')
                ->orderBy('name')
                ->get(['id', 'name', 'admin_role'])
                ->map(fn (User $user): array => [
                    'value' => $user->id,
                    'label' => $user->name.' - '.($user->admin_role?->label() ?? 'Staff'),
                ])
                ->values()
                ->all(),
        ];
    }

    private function dateValue(mixed $value): string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->toDateString()
            : '';
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function json(array $value): string
    {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }
}
