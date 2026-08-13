<?php

namespace App\Http\Resources\AdminConsole;

use App\Enums\SourceType;
use App\Models\SourceRecord;
use App\Services\AdminReferenceDataRegistry;
use App\Services\AdminReferenceDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReferenceDataFormResource
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
        private readonly ?Model $record,
        private readonly string $mode,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $registry = app(AdminReferenceDataRegistry::class);
        $usageCount = $this->record instanceof Model
            ? app(AdminReferenceDataService::class)->usageCount($this->record, $this->config)
            : 0;

        return [
            'mode' => $this->mode,
            'title' => $this->record instanceof Model
                ? 'Edit '.$registry->titleFor($this->record, $this->config)
                : 'Create '.$this->config['singular'],
            'action' => $this->record instanceof Model
                ? route('admin-console.reference-data.update', [
                    'type' => $this->config['type'],
                    'record' => $this->record->getKey(),
                ])
                : route('admin-console.reference-data.store', ['type' => $this->config['type']]),
            'method' => $this->record instanceof Model ? 'put' : 'post',
            'backHref' => $this->record instanceof Model
                ? route('admin-console.reference-data.show', [
                    'type' => $this->config['type'],
                    'record' => $this->record->getKey(),
                ])
                : route('admin-console.reference-data.index', ['type' => $this->config['type']]),
            'indexHref' => route('admin-console.reference-data.index', ['type' => $this->config['type']]),
            'definition' => [
                'type' => $this->config['type'],
                'label' => $this->config['label'],
                'singular' => $this->config['singular'],
                'description' => $this->config['description'],
                'fields' => $this->fields(),
                'options' => $this->config['options'] ?? [],
                'allTypes' => $registry->navigationOptions(),
            ],
            'values' => $this->values(),
            'usage' => [
                'count' => $usageCount,
                'warning' => $usageCount > 0
                    ? 'This record is attached to existing content. Deactivation is allowed only with an operational reason and will be audited.'
                    : 'This record has no current content usage.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fields(): array
    {
        return collect($this->config['fields'])
            ->map(fn (string $field): array => [
                'name' => $field,
                'label' => str($field)->replace('_', ' ')->headline()->toString(),
                'type' => match ($field) {
                    'description', 'notes' => 'textarea',
                    'is_active' => 'boolean',
                    'sort_order' => 'number',
                    'website_url' => 'url',
                    'value', 'tone' => 'select',
                    default => 'text',
                },
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function values(): array
    {
        $values = [];

        foreach ($this->config['fields'] as $field) {
            $value = $this->record?->getAttribute($field);
            $values[$field] = $value instanceof \BackedEnum ? $value->value : ($value ?? $this->defaultValue($field));
        }

        $source = $this->latestSource();

        return [
            ...$values,
            'source_record' => [
                'title' => $source?->title ?? '',
                'url' => $source?->url ?? '',
                'source_type' => $source?->source_type->value ?? SourceType::PrimaryOfficial->value,
                'source_tier' => $source?->source_tier ?? 'tier_1',
                'public_status' => $source?->public_status ?? 'published',
                'description' => $source?->description ?? '',
                'verified_at' => $this->dateValue($source?->verified_at),
                'next_review_at' => $this->dateValue($source?->next_review_at),
            ],
        ];
    }

    private function latestSource(): ?SourceRecord
    {
        if (! $this->record instanceof Model || ! $this->record->relationLoaded('sourceRecords')) {
            return null;
        }

        return $this->record->sourceRecords
            ->sortByDesc(fn (SourceRecord $record): int => $record->verified_at?->getTimestamp() ?? 0)
            ->first();
    }

    private function defaultValue(string $field): mixed
    {
        return match ($field) {
            'is_active' => true,
            'sort_order' => 0,
            default => '',
        };
    }

    private function dateValue(mixed $value): string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->toDateString()
            : '';
    }
}
