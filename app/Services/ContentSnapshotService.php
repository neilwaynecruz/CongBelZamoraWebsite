<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Models\ContentApproval;
use App\Models\ContentSnapshot;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ContentSnapshotService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentGovernanceService $governance,
    ) {}

    public function capture(
        ContentApproval $approval,
        ContentSnapshotType $type,
        ?User $actor = null,
        ?string $changeSummary = null,
    ): ContentSnapshot {
        $approval->loadMissing('approvable');

        $approvable = $approval->approvable;

        if (! $approvable instanceof Model) {
            throw new InvalidArgumentException('Cannot capture a content snapshot without a linked approvable record.');
        }

        return DB::transaction(function () use ($approval, $type, $actor, $changeSummary, $approvable): ContentSnapshot {
            $versionNumber = $this->nextVersionNumber($approvable);
            $payload = $this->normalizedPayload($approvable);
            $previous = $this->latestSnapshotFor($approvable);
            $diff = $this->diffPayloads(
                data_get($previous?->payload, 'attributes', []),
                data_get($payload, 'attributes', []),
            );
            $issues = $this->governance->blockingIssues($approval);

            $snapshot = ContentSnapshot::query()->create([
                'content_approval_id' => $approval->id,
                'approvable_type' => $approvable::class,
                'approvable_id' => $approvable->getKey(),
                'version_number' => $versionNumber,
                'approval_revision_number' => max(1, (int) $approval->revision_number),
                'snapshot_type' => $type,
                'captured_by_id' => $actor?->id,
                'change_summary' => $changeSummary ?: $approval->change_summary,
                'payload' => $payload,
                'payload_hash' => $this->payloadHash($payload),
                'diff_from_previous' => $diff,
                'is_publishable' => $issues === [],
                'governance_issues' => $issues,
                'captured_at' => now(),
                'published_at' => $type === ContentSnapshotType::Published ? now() : null,
            ]);

            $this->auditLog->record(
                event: 'content.snapshot_captured',
                auditable: $snapshot,
                actor: $actor,
                newValues: [
                    'content_approval_id' => $approval->id,
                    'approvable_type' => $approvable::class,
                    'approvable_id' => $approvable->getKey(),
                    'snapshot_type' => $type->value,
                    'version_number' => $snapshot->version_number,
                    'is_publishable' => $snapshot->is_publishable,
                    'diff_count' => count($diff),
                ],
            );

            return $snapshot;
        });
    }

    /**
     * @return array<string, array{from: mixed, to: mixed}>
     */
    public function compareCurrentToPublished(ContentApproval $approval): array
    {
        $approval->loadMissing('approvable');
        $approvable = $approval->approvable;

        if (! $approvable instanceof Model) {
            return [];
        }

        $published = ContentSnapshot::query()
            ->where('approvable_type', $approvable::class)
            ->where('approvable_id', $approvable->getKey())
            ->where('snapshot_type', ContentSnapshotType::Published->value)
            ->latest('version_number')
            ->first();

        return $this->diffPayloads(
            data_get($published?->payload, 'attributes', []),
            data_get($this->normalizedPayload($approvable), 'attributes', []),
        );
    }

    public function rollbackToSnapshot(
        ContentApproval $approval,
        ContentSnapshot $snapshot,
        ?User $actor = null,
        ?string $reason = null,
    ): ContentSnapshot {
        $approval->loadMissing('approvable');
        $approvable = $approval->approvable;

        if (! $approvable instanceof Model) {
            throw new InvalidArgumentException('Cannot rollback content without a linked approvable record.');
        }

        if ($snapshot->approvable_type !== $approvable::class || (int) $snapshot->approvable_id !== (int) $approvable->getKey()) {
            throw new InvalidArgumentException('The selected snapshot does not belong to this content record.');
        }

        if (! in_array($snapshot->snapshot_type, [ContentSnapshotType::Approved, ContentSnapshotType::Published], true)) {
            throw new InvalidArgumentException('Only an approved or previously published snapshot can be restored.');
        }

        if (! $snapshot->is_publishable) {
            throw new InvalidArgumentException('This snapshot did not pass governance checks when it was captured and cannot be restored.');
        }

        return DB::transaction(function () use ($approval, $snapshot, $actor, $reason, $approvable): ContentSnapshot {
            $before = $this->normalizedPayload($approvable);
            $payloadAttributes = data_get($snapshot->payload, 'attributes', []);
            $restoredAttributes = Arr::only(
                is_array($payloadAttributes) ? $payloadAttributes : [],
                $approvable->getFillable(),
            );

            $approvable->forceFill($restoredAttributes)->save();

            if (array_key_exists('published_at', $approvable->getAttributes())) {
                $approvable->forceFill([
                    'published_at' => $approval->status === ContentApprovalStatus::Published
                        ? ($approval->published_at ?? now())
                        : null,
                ])->save();
            }

            $approval->forceFill([
                'rollback_snapshot_id' => $snapshot->id,
                'change_summary' => $reason ?: 'Rolled back to snapshot v'.$snapshot->version_number.'.',
            ])->save();

            $approval->unsetRelation('approvable');
            $approval->load('approvable');
            $this->governance->assertPublishable($approval);

            $rollbackSnapshot = $this->capture(
                approval: $approval->fresh(['approvable']) ?? $approval,
                type: ContentSnapshotType::Rollback,
                actor: $actor,
                changeSummary: $reason ?: 'Rolled back to snapshot v'.$snapshot->version_number.'.',
            );

            $rollbackSnapshot->forceFill(['rolled_back_at' => now()])->save();

            $this->auditLog->record(
                event: 'content.rollback',
                auditable: $approval,
                actor: $actor,
                oldValues: [
                    'payload_hash' => $this->payloadHash($before),
                ],
                newValues: [
                    'rollback_snapshot_id' => $snapshot->id,
                    'rollback_version_number' => $snapshot->version_number,
                    'new_snapshot_id' => $rollbackSnapshot->id,
                    'reason' => $reason,
                ],
            );

            return $rollbackSnapshot;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function normalizedPayload(Model $model): array
    {
        $attributes = collect($model->attributesToArray())
            ->reject(fn (mixed $value, string $key): bool => in_array($key, ['updated_at', 'deleted_at'], true))
            ->mapWithKeys(fn (mixed $value, string $key): array => [$key => $this->normalizeValue($value)])
            ->sortKeys()
            ->all();

        return [
            'model' => $model::class,
            'model_label' => Str::headline(class_basename($model)),
            'id' => $model->getKey(),
            'content_label' => $this->contentLabel($model),
            'captured_at' => now()->toIso8601String(),
            'attributes' => $attributes,
        ];
    }

    /**
     * @param  array<string, mixed>  $from
     * @param  array<string, mixed>  $to
     * @return array<string, array{from: mixed, to: mixed}>
     */
    private function diffPayloads(array $from, array $to): array
    {
        $diff = [];

        foreach (array_unique([...array_keys($from), ...array_keys($to)]) as $key) {
            $before = $from[$key] ?? null;
            $after = $to[$key] ?? null;

            if ($before !== $after) {
                $diff[$key] = [
                    'from' => $before,
                    'to' => $after,
                ];
            }
        }

        ksort($diff);

        return $diff;
    }

    private function latestSnapshotFor(Model $model): ?ContentSnapshot
    {
        return ContentSnapshot::query()
            ->where('approvable_type', $model::class)
            ->where('approvable_id', $model->getKey())
            ->latest('version_number')
            ->first();
    }

    private function nextVersionNumber(Model $model): int
    {
        return ((int) ContentSnapshot::query()
            ->where('approvable_type', $model::class)
            ->where('approvable_id', $model->getKey())
            ->max('version_number')) + 1;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function payloadHash(array $payload): string
    {
        return hash('sha256', json_encode($payload['attributes'] ?? $payload, JSON_THROW_ON_ERROR));
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_array($value)) {
            ksort($value);

            return array_map(fn (mixed $item): mixed => $this->normalizeValue($item), $value);
        }

        return $value;
    }

    private function contentLabel(Model $model): string
    {
        foreach (['title', 'public_title', 'official_title', 'label', 'name', 'reference_code', 'bill_number'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return Str::headline(class_basename($model)).' #'.$model->getKey();
    }
}
