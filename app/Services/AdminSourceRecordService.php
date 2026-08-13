<?php

namespace App\Services;

use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class AdminSourceRecordService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): SourceRecord
    {
        return $this->save(new SourceRecord, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(SourceRecord $sourceRecord, array $payload, User $actor): SourceRecord
    {
        return $this->save($sourceRecord, $payload, $actor, false);
    }

    public function archive(SourceRecord $sourceRecord, User $actor): SourceRecord
    {
        return DB::transaction(function () use ($sourceRecord, $actor): SourceRecord {
            $before = $this->snapshot($sourceRecord);
            $sourceRecord->delete();

            $this->auditLog->record(
                event: 'source_record.archived',
                auditable: $sourceRecord,
                actor: $actor,
                oldValues: $before,
                newValues: ['deleted_at' => now()->toIso8601String()],
                metadata: ['custom_admin_console' => true],
            );

            return $sourceRecord;
        });
    }

    public function restore(SourceRecord $sourceRecord, User $actor): SourceRecord
    {
        return DB::transaction(function () use ($sourceRecord, $actor): SourceRecord {
            $before = $this->snapshot($sourceRecord);
            $sourceRecord->restore();

            $this->auditLog->record(
                event: 'source_record.restored',
                auditable: $sourceRecord,
                actor: $actor,
                oldValues: $before,
                newValues: $this->snapshot($sourceRecord->fresh() ?? $sourceRecord),
                metadata: ['custom_admin_console' => true],
            );

            return $sourceRecord;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function save(SourceRecord $sourceRecord, array $payload, User $actor, bool $creating): SourceRecord
    {
        return DB::transaction(function () use ($sourceRecord, $payload, $actor, $creating): SourceRecord {
            $before = $sourceRecord->exists ? $this->snapshot($sourceRecord) : [];

            $sourceRecord->fill([
                ...Arr::only($payload, [
                    'title',
                    'url',
                    'source_type',
                    'source_tier',
                    'description',
                    'content_owner_id',
                    'verified_by_id',
                    'verified_at',
                    'last_reviewed_at',
                    'next_review_at',
                    'effective_at',
                    'expires_at',
                    'public_status',
                    'internal_notes',
                ]),
                'metadata' => $payload['metadata'] ?? null,
            ]);
            $sourceRecord->content_owner_id ??= $actor->id;
            $sourceRecord->verified_by_id ??= filled($sourceRecord->verified_at) ? $actor->id : null;

            $sourceRecord->save();

            $this->auditLog->record(
                event: $creating ? 'source_record.created' : 'source_record.updated',
                auditable: $sourceRecord,
                actor: $actor,
                oldValues: $before,
                newValues: $this->snapshot($sourceRecord),
                metadata: ['custom_admin_console' => true],
            );

            return $sourceRecord->fresh(['contentOwner:id,name', 'verifier:id,name', 'sourceable']) ?? $sourceRecord;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(SourceRecord $sourceRecord): array
    {
        return [
            'title' => $sourceRecord->title,
            'url' => $sourceRecord->url,
            'source_type' => $sourceRecord->source_type?->value,
            'source_tier' => $sourceRecord->source_tier,
            'public_status' => $sourceRecord->public_status,
            'content_owner_id' => $sourceRecord->content_owner_id,
            'verified_by_id' => $sourceRecord->verified_by_id,
            'verified_at' => $sourceRecord->verified_at?->toIso8601String(),
            'next_review_at' => $sourceRecord->next_review_at?->toIso8601String(),
            'expires_at' => $sourceRecord->expires_at?->toIso8601String(),
            'deleted_at' => $sourceRecord->deleted_at?->toIso8601String(),
        ];
    }
}
