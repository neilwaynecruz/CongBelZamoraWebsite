<?php

namespace App\Services;

use App\Models\NewsMediaAsset;
use App\Models\NewsRecord;
use App\Models\Project;
use App\Models\ProjectEvidence;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class AdminMediaLibraryService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveNewsAsset(
        NewsRecord $record,
        ?NewsMediaAsset $asset,
        array $payload,
        User $actor,
        Request $request,
    ): NewsMediaAsset {
        $asset ??= $record->mediaAssets()->make(['media_type' => 'image']);

        return $this->saveRecord(
            record: $asset,
            payload: $payload,
            actor: $actor,
            request: $request,
            directory: (string) config('belzamora.media.news_directory', 'approved-media/news'),
            eventPrefix: 'news_media_asset',
            parentAttributes: ['news_record_id' => $record->id, 'media_type' => 'image'],
        );
    }

    public function removeNewsAsset(NewsMediaAsset $asset, User $actor, Request $request): void
    {
        $this->removeRecord($asset, $actor, $request, 'news_media_asset');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function saveProjectEvidence(
        Project $project,
        ?ProjectEvidence $evidence,
        array $payload,
        User $actor,
        Request $request,
    ): ProjectEvidence {
        $evidence ??= $project->evidences()->make();

        return $this->saveRecord(
            record: $evidence,
            payload: $payload,
            actor: $actor,
            request: $request,
            directory: (string) config('belzamora.media.project_evidence_directory', 'approved-media/project-evidence'),
            eventPrefix: 'project_evidence',
            parentAttributes: ['project_id' => $project->id],
        );
    }

    public function removeProjectEvidence(ProjectEvidence $evidence, User $actor, Request $request): void
    {
        $this->removeRecord($evidence, $actor, $request, 'project_evidence');
    }

    /**
     * @template TModel of NewsMediaAsset|ProjectEvidence
     *
     * @param  TModel  $record
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $parentAttributes
     * @return TModel
     */
    private function saveRecord(
        NewsMediaAsset|ProjectEvidence $record,
        array $payload,
        User $actor,
        Request $request,
        string $directory,
        string $eventPrefix,
        array $parentAttributes,
    ): NewsMediaAsset|ProjectEvidence {
        $upload = $request->file('media_file');
        $newPath = null;

        if ($upload instanceof UploadedFile) {
            $stored = $upload->store($directory, 'public');

            if ($stored === false) {
                throw new RuntimeException('Unable to store approved media upload.');
            }

            $newPath = $stored;
        }

        $oldPath = $record->media_path;
        $oldValues = $record->exists ? $this->snapshot($record) : [];
        $creating = ! $record->exists;

        try {
            $saved = DB::transaction(function () use (
                $record,
                $payload,
                $actor,
                $request,
                $eventPrefix,
                $parentAttributes,
                $newPath,
                $oldValues,
                $creating,
            ): NewsMediaAsset|ProjectEvidence {
                $record->fill(array_merge($parentAttributes, Arr::only($payload, [
                    'title',
                    'source_label',
                    'source_url',
                    'caption',
                    'alt_text',
                    'rights_note',
                    'verified_at',
                    'sort_order',
                ])));

                if ($record instanceof NewsMediaAsset) {
                    $record->media_type = 'image';
                }

                if ($newPath !== null) {
                    $record->media_path = $newPath;
                } elseif ((bool) ($payload['remove_file'] ?? false)) {
                    $record->media_path = null;
                }

                $record->verified_by_id = filled($payload['verified_at'] ?? null) ? $actor->id : null;
                $record->metadata = array_merge((array) $record->metadata, [
                    'custom_admin_console' => true,
                    'storage_disk' => 'public',
                    'visibility' => 'public',
                    'sensitive_files_disallowed' => true,
                ]);
                $record->save();

                $this->auditLog->record(
                    event: $eventPrefix.'.'.($creating ? 'created' : 'updated'),
                    auditable: $record,
                    actor: $actor,
                    request: $request,
                    oldValues: $creating ? [] : $oldValues,
                    newValues: $this->snapshot($record),
                    metadata: [
                        'custom_admin_console' => true,
                        'storage_disk' => 'public',
                        'visibility' => 'public',
                        'file_replaced' => $newPath !== null,
                        'file_removed' => (bool) ($payload['remove_file'] ?? false),
                    ],
                );

                return $record->fresh('verifier') ?? $record;
            });
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if (($newPath !== null || (bool) ($payload['remove_file'] ?? false))
            && filled($oldPath)
            && $oldPath !== $saved->media_path) {
            Storage::disk('public')->delete($oldPath);
        }

        return $saved;
    }

    private function removeRecord(
        NewsMediaAsset|ProjectEvidence $record,
        User $actor,
        Request $request,
        string $eventPrefix,
    ): void {
        $oldValues = $this->snapshot($record);
        $path = $record->media_path;

        DB::transaction(function () use ($record, $actor, $request, $eventPrefix, $oldValues): void {
            $record->delete();

            $this->auditLog->record(
                event: $eventPrefix.'.removed',
                auditable: $record,
                actor: $actor,
                request: $request,
                oldValues: $oldValues,
                newValues: ['deleted_at' => now()->toIso8601String()],
                metadata: ['custom_admin_console' => true, 'file_removed' => true],
            );
        });

        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(NewsMediaAsset|ProjectEvidence $record): array
    {
        return [
            'id' => $record->id,
            'title' => $record->title,
            'media_path' => $record->media_path,
            'source_label' => $record->source_label,
            'source_url' => $record->source_url,
            'caption' => $record->caption,
            'alt_text' => $record->alt_text,
            'rights_note' => $record->rights_note,
            'verified_at' => $record->verified_at?->toIso8601String(),
            'verified_by_id' => $record->verified_by_id,
            'sort_order' => $record->sort_order,
        ];
    }
}
