<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\Project;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminProjectEditorService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentApprovalWorkflowService $workflow,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): Project
    {
        return $this->save(new Project, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(Project $project, array $payload, User $actor): Project
    {
        return $this->save($project, $payload, $actor, false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function save(Project $project, array $payload, User $actor, bool $creating): Project
    {
        return DB::transaction(function () use ($project, $payload, $actor, $creating): Project {
            $before = $project->exists ? Arr::only($project->getOriginal(), [
                'title',
                'slug',
                'barangay',
                'project_status_id',
                'source_url',
                'verified_at',
                'review_due_at',
            ]) : [];

            $project->fill($this->projectAttributes($payload));
            $project->content_owner_id ??= $actor->id;

            if ($creating) {
                $project->approved_by_id = null;
            }

            $project->save();

            $this->replaceEvidences($project, $payload['evidences'] ?? [], $actor);
            $this->syncSourceRecord($project, $payload['source_record'] ?? [], $actor);

            $this->auditLog->record(
                event: $creating ? 'project.created' : 'project.updated',
                auditable: $project,
                actor: $actor,
                oldValues: $before,
                newValues: [
                    'title' => $project->title,
                    'slug' => $project->slug,
                    'barangay' => $project->barangay,
                    'project_status_id' => $project->project_status_id,
                    'source_url' => $project->source_url,
                    'verified_at' => $project->verified_at?->toIso8601String(),
                    'review_due_at' => $project->review_due_at?->toIso8601String(),
                    'evidences_count' => count($payload['evidences'] ?? []),
                ],
                metadata: ['custom_admin_console' => true],
            );

            if ((bool) data_get($payload, 'approval.submit_for_review', false)) {
                $this->submitForReview($project, $payload, $actor);
            }

            return $project->fresh([
                'category:id,name',
                'status:id,label,value,tone',
                'primaryPartner:id,name',
                'contentOwner:id,name',
                'approver:id,name',
                'evidences.verifier:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ]) ?? $project;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function projectAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'project_category_id',
            'project_status_id',
            'primary_partner_id',
            'title',
            'slug',
            'barangay',
            'location',
            'responsible_office',
            'partner_label',
            'summary',
            'evidence_summary',
            'impact_note',
            'image_guidance',
            'source_label',
            'source_url',
            'verified_at',
            'review_due_at',
            'published_at',
            'sort_order',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $evidences
     */
    private function replaceEvidences(Project $project, array $evidences, User $actor): void
    {
        $project->evidences()->delete();

        foreach (array_values($evidences) as $index => $evidence) {
            $project->evidences()->create([
                'title' => (string) $evidence['title'],
                'source_label' => (string) $evidence['source_label'],
                'source_url' => (string) $evidence['source_url'],
                'caption' => filled($evidence['caption'] ?? null) ? (string) $evidence['caption'] : null,
                'alt_text' => filled($evidence['alt_text'] ?? null) ? (string) $evidence['alt_text'] : null,
                'rights_note' => filled($evidence['rights_note'] ?? null) ? (string) $evidence['rights_note'] : null,
                'media_path' => filled($evidence['media_path'] ?? null) ? (string) $evidence['media_path'] : null,
                'verified_at' => filled($evidence['verified_at'] ?? null) ? (string) $evidence['verified_at'] : null,
                'verified_by_id' => filled($evidence['verified_at'] ?? null) ? $actor->id : null,
                'sort_order' => $index + 1,
                'metadata' => ['custom_admin_console' => true],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function syncSourceRecord(Project $project, array $source, User $actor): SourceRecord
    {
        /** @var SourceRecord|null $record */
        $record = $project->sourceRecords()
            ->latest('verified_at')
            ->first();

        $record ??= $project->sourceRecords()->make();
        $record->fill([
            'title' => (string) $source['title'],
            'url' => (string) $source['url'],
            'source_type' => (string) $source['source_type'],
            'source_tier' => (string) $source['source_tier'],
            'public_status' => (string) $source['public_status'],
            'description' => filled($source['description'] ?? null) ? (string) $source['description'] : null,
            'content_owner_id' => $project->content_owner_id ?? $actor->id,
            'verified_by_id' => $actor->id,
            'verified_at' => (string) $source['verified_at'],
            'last_reviewed_at' => now(),
            'next_review_at' => (string) $source['next_review_at'],
            'metadata' => ['custom_admin_console' => true],
        ]);
        $record->save();

        return $record;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function submitForReview(Project $project, array $payload, User $actor): ContentApproval
    {
        Gate::forUser($actor)->authorize('create', ContentApproval::class);

        $approval = $project->approvalRecords()->create([
            'status' => ContentApprovalStatus::Draft,
            'revision_number' => $this->nextRevisionNumber($project),
            'submitted_by_id' => $actor->id,
            'change_summary' => (string) data_get($payload, 'approval.change_summary'),
            'is_fast_track' => (bool) data_get($payload, 'approval.is_fast_track', false),
            'valid_until' => filled(data_get($payload, 'approval.valid_until'))
                ? (string) data_get($payload, 'approval.valid_until')
                : null,
            'metadata' => ['custom_admin_console' => true, 'module' => 'projects'],
        ]);

        Gate::forUser($actor)->authorize('submit', $approval);

        return $this->workflow->submitForReview(
            $approval,
            $actor,
            (string) data_get($payload, 'approval.change_summary'),
        );
    }

    private function nextRevisionNumber(Project $project): int
    {
        return ((int) $project->approvalRecords()->max('revision_number')) + 1;
    }
}
