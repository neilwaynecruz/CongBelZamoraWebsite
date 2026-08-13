<?php

namespace App\Services;

use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdminReferenceDataService
{
    public function __construct(
        private readonly AdminReferenceDataRegistry $registry,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     */
    public function create(array $config, array $payload, User $actor): Model
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $config['model'];

        return $this->save(new $modelClass, $config, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     */
    public function update(Model $record, array $config, array $payload, User $actor): Model
    {
        return $this->save($record, $config, $payload, $actor, false);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $payload
     */
    private function save(Model $record, array $config, array $payload, User $actor, bool $creating): Model
    {
        return DB::transaction(function () use ($record, $config, $payload, $actor, $creating): Model {
            $before = $record->exists ? $this->snapshot($record, $config) : [];

            $record->fill(Arr::only($payload, $config['fields']));
            $record->save();

            $this->syncSourceRecord($record, $config, $payload['source_record'] ?? [], $actor);

            $this->auditLog->record(
                event: $creating ? 'reference_data.created' : 'reference_data.updated',
                auditable: $record,
                actor: $actor,
                oldValues: $before,
                newValues: $this->snapshot($record, $config),
                metadata: [
                    'custom_admin_console' => true,
                    'reference_type' => $config['type'],
                    'usage_count' => $this->usageCount($record, $config),
                ],
            );

            return $record->fresh(['sourceRecords.verifier:id,name']) ?? $record;
        });
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function usageCount(Model $record, array $config): int
    {
        if (isset($config['usageCallback']) && is_callable($config['usageCallback'])) {
            return (int) $config['usageCallback']($record);
        }

        return collect($config['usage'] ?? [])
            ->keys()
            ->sum(fn (string $relation): int => method_exists($record, $relation)
                ? (int) $record->{$relation}()->count()
                : 0);
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $source
     */
    private function syncSourceRecord(Model $record, array $config, array $source, User $actor): ?SourceRecord
    {
        if (! filled($source['title'] ?? null) && ! filled($source['url'] ?? null)) {
            return null;
        }

        /** @var SourceRecord|null $sourceRecord */
        $sourceRecord = $record->sourceRecords()
            ->latest('verified_at')
            ->first();

        $sourceRecord ??= $record->sourceRecords()->make();
        $sourceRecord->fill([
            'title' => (string) ($source['title'] ?: $this->registry->titleFor($record, $config).' source'),
            'url' => filled($source['url'] ?? null) ? (string) $source['url'] : null,
            'source_type' => (string) $source['source_type'],
            'source_tier' => (string) $source['source_tier'],
            'public_status' => (string) $source['public_status'],
            'description' => filled($source['description'] ?? null) ? (string) $source['description'] : null,
            'content_owner_id' => $actor->id,
            'verified_by_id' => $actor->id,
            'verified_at' => filled($source['verified_at'] ?? null) ? (string) $source['verified_at'] : null,
            'last_reviewed_at' => now(),
            'next_review_at' => filled($source['next_review_at'] ?? null) ? (string) $source['next_review_at'] : null,
            'metadata' => [
                'custom_admin_console' => true,
                'module' => 'reference_data',
                'reference_type' => $config['type'],
            ],
        ]);
        $sourceRecord->save();

        return $sourceRecord;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    private function snapshot(Model $record, array $config): array
    {
        return Arr::only($record->getAttributes(), $config['fields']);
    }
}
