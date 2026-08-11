<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\LegislativeRecord;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminLegislativeRecordEditorService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentApprovalWorkflowService $workflow,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): LegislativeRecord
    {
        return $this->save(new LegislativeRecord, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(LegislativeRecord $record, array $payload, User $actor): LegislativeRecord
    {
        return $this->save($record, $payload, $actor, false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function save(LegislativeRecord $record, array $payload, User $actor, bool $creating): LegislativeRecord
    {
        return DB::transaction(function () use ($record, $payload, $actor, $creating): LegislativeRecord {
            $before = $record->exists ? Arr::only($record->getOriginal(), [
                'bill_number',
                'public_title',
                'legislative_role_id',
                'legislative_status_id',
                'legislative_topic_id',
                'official_source_url',
                'verified_at',
                'review_due_at',
            ]) : [];

            $record->fill($this->recordAttributes($payload));
            $record->content_owner_id ??= $actor->id;

            if ($creating) {
                $record->approved_by_id = null;
            }

            $record->save();

            $this->replaceSourceHistories($record, $payload['source_histories'] ?? [], $actor);
            $this->syncSourceRecord($record, $payload['source_record'] ?? [], $actor);

            $this->auditLog->record(
                event: $creating ? 'legislative_record.created' : 'legislative_record.updated',
                auditable: $record,
                actor: $actor,
                oldValues: $before,
                newValues: [
                    'bill_number' => $record->bill_number,
                    'public_title' => $record->public_title,
                    'legislative_role_id' => $record->legislative_role_id,
                    'legislative_status_id' => $record->legislative_status_id,
                    'legislative_topic_id' => $record->legislative_topic_id,
                    'official_source_url' => $record->official_source_url,
                    'verified_at' => $record->verified_at?->toIso8601String(),
                    'review_due_at' => $record->review_due_at?->toIso8601String(),
                    'source_histories_count' => count($payload['source_histories'] ?? []),
                ],
                metadata: ['custom_admin_console' => true],
            );

            if ((bool) data_get($payload, 'approval.submit_for_review', false)) {
                $this->submitForReview($record, $payload, $actor);
            }

            return $record->fresh($this->relations()) ?? $record;
        });
    }

    /**
     * @return list<string>
     */
    public function relations(): array
    {
        return [
            'role:id,label,value',
            'status:id,label,tone',
            'topic:id,name',
            'contentOwner:id,name',
            'approver:id,name',
            'sourceHistories.checker:id,name',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function recordAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'legislative_role_id',
            'legislative_status_id',
            'legislative_topic_id',
            'bill_number',
            'slug',
            'official_title',
            'public_title',
            'congress',
            'committee',
            'filed_at_label',
            'filed_at',
            'verified_at',
            'plain_language_summary',
            'why_it_matters',
            'san_juan_relevance',
            'official_source_label',
            'official_source_url',
            'published_at',
            'review_due_at',
            'sort_order',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $histories
     */
    private function replaceSourceHistories(LegislativeRecord $record, array $histories, User $actor): void
    {
        $record->sourceHistories()->delete();

        foreach (array_values($histories) as $history) {
            $record->sourceHistories()->create([
                'source_label' => (string) $history['source_label'],
                'source_url' => (string) $history['source_url'],
                'status_snapshot' => (string) $history['status_snapshot'],
                'checked_by_id' => filled($history['checked_at'] ?? null) ? $actor->id : null,
                'checked_at' => filled($history['checked_at'] ?? null) ? (string) $history['checked_at'] : null,
                'notes' => filled($history['notes'] ?? null) ? (string) $history['notes'] : null,
                'metadata' => ['custom_admin_console' => true],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function syncSourceRecord(LegislativeRecord $record, array $source, User $actor): SourceRecord
    {
        /** @var SourceRecord|null $sourceRecord */
        $sourceRecord = $record->sourceRecords()
            ->latest('verified_at')
            ->first();

        $sourceRecord ??= $record->sourceRecords()->make();
        $sourceRecord->fill([
            'title' => (string) $source['title'],
            'url' => (string) $source['url'],
            'source_type' => (string) $source['source_type'],
            'source_tier' => (string) $source['source_tier'],
            'public_status' => (string) $source['public_status'],
            'description' => filled($source['description'] ?? null) ? (string) $source['description'] : null,
            'content_owner_id' => $record->content_owner_id ?? $actor->id,
            'verified_by_id' => $actor->id,
            'verified_at' => (string) $source['verified_at'],
            'last_reviewed_at' => now(),
            'next_review_at' => (string) $source['next_review_at'],
            'metadata' => ['custom_admin_console' => true],
        ]);
        $sourceRecord->save();

        return $sourceRecord;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function submitForReview(LegislativeRecord $record, array $payload, User $actor): ContentApproval
    {
        Gate::forUser($actor)->authorize('create', ContentApproval::class);

        $approval = $record->approvalRecords()->create([
            'status' => ContentApprovalStatus::Draft,
            'revision_number' => $this->nextRevisionNumber($record),
            'submitted_by_id' => $actor->id,
            'change_summary' => (string) data_get($payload, 'approval.change_summary'),
            'is_fast_track' => (bool) data_get($payload, 'approval.is_fast_track', false),
            'valid_until' => filled(data_get($payload, 'approval.valid_until'))
                ? (string) data_get($payload, 'approval.valid_until')
                : null,
            'metadata' => ['custom_admin_console' => true, 'module' => 'legislation'],
        ]);

        Gate::forUser($actor)->authorize('submit', $approval);

        return $this->workflow->submitForReview(
            $approval,
            $actor,
            (string) data_get($payload, 'approval.change_summary'),
        );
    }

    private function nextRevisionNumber(LegislativeRecord $record): int
    {
        return ((int) $record->approvalRecords()->max('revision_number')) + 1;
    }
}
