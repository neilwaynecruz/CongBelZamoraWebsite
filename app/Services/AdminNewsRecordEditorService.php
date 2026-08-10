<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\NewsRecord;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminNewsRecordEditorService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentApprovalWorkflowService $workflow,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): NewsRecord
    {
        return $this->save(new NewsRecord, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(NewsRecord $record, array $payload, User $actor): NewsRecord
    {
        return $this->save($record, $payload, $actor, false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function save(NewsRecord $record, array $payload, User $actor, bool $creating): NewsRecord
    {
        return DB::transaction(function () use ($record, $payload, $actor, $creating): NewsRecord {
            $before = $record->exists ? Arr::only($record->getOriginal(), [
                'title',
                'slug',
                'news_status_id',
                'source_url',
                'source_verified_at',
                'review_due_at',
            ]) : [];

            $record->fill($this->recordAttributes($payload));
            $record->content_owner_id ??= $actor->id;

            if ($creating) {
                $record->approved_by_id = null;
            }

            $record->save();

            $this->replaceMediaAssets($record, $payload['media_assets'] ?? [], $actor);
            $this->syncSourceRecord($record, $payload['source_record'] ?? [], $actor);

            $this->auditLog->record(
                event: $creating ? 'news.created' : 'news.updated',
                auditable: $record,
                actor: $actor,
                oldValues: $before,
                newValues: [
                    'title' => $record->title,
                    'slug' => $record->slug,
                    'news_status_id' => $record->news_status_id,
                    'source_url' => $record->source_url,
                    'source_verified_at' => $record->source_verified_at?->toIso8601String(),
                    'review_due_at' => $record->review_due_at?->toIso8601String(),
                    'media_assets_count' => count($payload['media_assets'] ?? []),
                ],
                metadata: ['custom_admin_console' => true],
            );

            if ((bool) data_get($payload, 'approval.submit_for_review', false)) {
                $this->submitForReview($record, $payload, $actor);
            }

            return $record->fresh([
                'category:id,name',
                'status:id,label,value,tone',
                'contentOwner:id,name',
                'approver:id,name',
                'mediaAssets.verifier:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ]) ?? $record;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function recordAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'news_category_id',
            'news_status_id',
            'title',
            'slug',
            'published_at_label',
            'published_at',
            'public_updated_at_label',
            'public_updated_at',
            'source_label',
            'source_url',
            'summary',
            'official_update',
            'next_step',
            'stale_note',
            'owner_label',
            'source_verified_at',
            'review_due_at',
            'sort_order',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $assets
     */
    private function replaceMediaAssets(NewsRecord $record, array $assets, User $actor): void
    {
        $record->mediaAssets()->delete();

        foreach (array_values($assets) as $index => $asset) {
            $record->mediaAssets()->create([
                'title' => (string) $asset['title'],
                'media_type' => (string) $asset['media_type'],
                'media_path' => filled($asset['media_path'] ?? null) ? (string) $asset['media_path'] : null,
                'source_label' => (string) $asset['source_label'],
                'source_url' => (string) $asset['source_url'],
                'caption' => filled($asset['caption'] ?? null) ? (string) $asset['caption'] : null,
                'alt_text' => filled($asset['alt_text'] ?? null) ? (string) $asset['alt_text'] : null,
                'rights_note' => filled($asset['rights_note'] ?? null) ? (string) $asset['rights_note'] : null,
                'verified_at' => filled($asset['verified_at'] ?? null) ? (string) $asset['verified_at'] : null,
                'verified_by_id' => filled($asset['verified_at'] ?? null) ? $actor->id : null,
                'sort_order' => $index + 1,
                'metadata' => ['custom_admin_console' => true],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function syncSourceRecord(NewsRecord $record, array $source, User $actor): SourceRecord
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
    private function submitForReview(NewsRecord $record, array $payload, User $actor): ContentApproval
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
            'metadata' => ['custom_admin_console' => true, 'module' => 'news'],
        ]);

        Gate::forUser($actor)->authorize('submit', $approval);

        return $this->workflow->submitForReview(
            $approval,
            $actor,
            (string) data_get($payload, 'approval.change_summary'),
        );
    }

    private function nextRevisionNumber(NewsRecord $record): int
    {
        return ((int) $record->approvalRecords()->max('revision_number')) + 1;
    }
}
