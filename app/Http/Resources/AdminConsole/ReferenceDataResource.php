<?php

namespace App\Http\Resources\AdminConsole;

use App\Models\SourceRecord;
use App\Services\AdminReferenceDataRegistry;
use App\Services\AdminReferenceDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @mixin Model
 */
class ReferenceDataResource extends JsonResource
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        mixed $resource,
        private readonly array $config,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $registry = app(AdminReferenceDataRegistry::class);
        $usageCount = app(AdminReferenceDataService::class)->usageCount($this->resource, $this->config);
        $latestSource = $this->latestSource();

        return [
            'id' => $this->getKey(),
            'type' => $this->config['type'],
            'title' => $registry->titleFor($this->resource, $this->config),
            'subtitle' => $registry->subtitleFor($this->resource, $this->config),
            'href' => route('admin-console.reference-data.show', [
                'type' => $this->config['type'],
                'record' => $this->getKey(),
            ]),
            'editHref' => route('admin-console.reference-data.edit', [
                'type' => $this->config['type'],
                'record' => $this->getKey(),
            ]),
            'values' => collect($this->config['fields'])
                ->mapWithKeys(fn (string $field): array => [$field => $this->value($field)])
                ->all(),
            'isActive' => (bool) $this->getAttribute('is_active'),
            'usage' => [
                'count' => $usageCount,
                'warning' => $usageCount > 0
                    ? 'This reference record is used by '.$usageCount.' existing record'.($usageCount === 1 ? '' : 's').'. Deactivation can hide filters or change staff choices, but existing content remains linked.'
                    : 'No active content currently references this record.',
            ],
            'source' => $latestSource ? [
                'id' => $latestSource->id,
                'title' => $latestSource->title,
                'url' => $latestSource->url,
                'status' => $latestSource->public_status,
                'type' => $latestSource->source_type->label(),
                'verifiedAt' => $this->formatDate($latestSource->verified_at),
                'nextReviewAt' => $this->formatDate($latestSource->next_review_at),
                'verifier' => $latestSource->verifier?->name,
            ] : null,
            'can' => [
                'view' => $request->user()?->can('view', $this->resource) ?? false,
                'update' => $request->user()?->can('update', $this->resource) ?? false,
            ],
        ];
    }

    private function latestSource(): ?SourceRecord
    {
        if (! $this->relationLoaded('sourceRecords')) {
            return null;
        }

        return $this->sourceRecords
            ->sortByDesc(fn (SourceRecord $record): int => $record->verified_at?->getTimestamp() ?? 0)
            ->first();
    }

    private function value(string $field): mixed
    {
        $value = $this->getAttribute($field);

        return $value instanceof \BackedEnum ? $value->value : $value;
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone('Asia/Manila')->format('M j, Y')
            : null;
    }
}
