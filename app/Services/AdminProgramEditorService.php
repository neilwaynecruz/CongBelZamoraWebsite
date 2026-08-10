<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\Program;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminProgramEditorService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentApprovalWorkflowService $workflow,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): Program
    {
        return $this->save(new Program, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(Program $program, array $payload, User $actor): Program
    {
        return $this->save($program, $payload, $actor, false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function save(Program $program, array $payload, User $actor, bool $creating): Program
    {
        return DB::transaction(function () use ($program, $payload, $actor, $creating): Program {
            $before = $program->exists ? Arr::only($program->getOriginal(), [
                'title',
                'slug',
                'status',
                'source_url',
                'verified_at',
                'review_due_at',
            ]) : [];

            $program->fill($this->programAttributes($payload));
            $program->content_owner_id ??= $actor->id;

            if ($creating) {
                $program->approved_by_id = null;
            }

            $program->save();

            $this->replaceRequirements($program, $payload['requirements'] ?? []);
            $this->replaceUpdates($program, $payload['updates'] ?? [], $actor);
            $this->syncSourceRecord($program, $payload['source_record'] ?? [], $actor);

            $this->auditLog->record(
                event: $creating ? 'program.created' : 'program.updated',
                auditable: $program,
                actor: $actor,
                oldValues: $before,
                newValues: [
                    'title' => $program->title,
                    'slug' => $program->slug,
                    'status' => $program->status->value,
                    'source_url' => $program->source_url,
                    'verified_at' => $program->verified_at?->toIso8601String(),
                    'review_due_at' => $program->review_due_at?->toIso8601String(),
                    'requirements_count' => count($payload['requirements'] ?? []),
                    'updates_count' => count($payload['updates'] ?? []),
                ],
                metadata: ['custom_admin_console' => true],
            );

            if ((bool) data_get($payload, 'approval.submit_for_review', false)) {
                $this->submitForReview($program, $payload, $actor);
            }

            return $program->fresh([
                'category:id,name',
                'contentOwner:id,name',
                'approver:id,name',
                'requirements',
                'updates',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ]) ?? $program;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function programAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'program_category_id',
            'title',
            'slug',
            'href',
            'sector',
            'status',
            'summary',
            'description',
            'responsible_office_type',
            'responsible_office_label',
            'partner_agency',
            'beneficiary_group',
            'application_period',
            'benefit',
            'source_label',
            'source_url',
            'verified_at',
            'review_due_at',
            'published_at',
            'is_featured',
            'sort_order',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $requirements
     */
    private function replaceRequirements(Program $program, array $requirements): void
    {
        $program->requirements()->delete();

        foreach (array_values($requirements) as $index => $requirement) {
            $program->requirements()->create([
                'group_title' => (string) $requirement['group_title'],
                'item_text' => (string) $requirement['item_text'],
                'note' => filled($requirement['note'] ?? null) ? (string) $requirement['note'] : null,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $updates
     */
    private function replaceUpdates(Program $program, array $updates, User $actor): void
    {
        $program->updates()->delete();

        foreach (array_values($updates) as $update) {
            $program->updates()->create([
                'title' => (string) $update['title'],
                'summary' => (string) $update['summary'],
                'status' => (string) $update['status'],
                'source_label' => filled($update['source_label'] ?? null) ? (string) $update['source_label'] : null,
                'source_url' => filled($update['source_url'] ?? null) ? (string) $update['source_url'] : null,
                'verified_at' => filled($update['verified_at'] ?? null) ? (string) $update['verified_at'] : null,
                'published_at' => filled($update['published_at'] ?? null) ? (string) $update['published_at'] : null,
                'content_owner_id' => $actor->id,
                'metadata' => ['custom_admin_console' => true],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function syncSourceRecord(Program $program, array $source, User $actor): SourceRecord
    {
        /** @var SourceRecord|null $record */
        $record = $program->sourceRecords()
            ->latest('verified_at')
            ->first();

        $record ??= $program->sourceRecords()->make();
        $record->fill([
            'title' => (string) $source['title'],
            'url' => (string) $source['url'],
            'source_type' => (string) $source['source_type'],
            'source_tier' => (string) $source['source_tier'],
            'public_status' => (string) $source['public_status'],
            'description' => filled($source['description'] ?? null) ? (string) $source['description'] : null,
            'content_owner_id' => $program->content_owner_id ?? $actor->id,
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
    private function submitForReview(Program $program, array $payload, User $actor): ContentApproval
    {
        Gate::forUser($actor)->authorize('create', ContentApproval::class);

        $approval = $program->approvalRecords()->create([
            'status' => ContentApprovalStatus::Draft,
            'revision_number' => $this->nextRevisionNumber($program),
            'submitted_by_id' => $actor->id,
            'change_summary' => (string) data_get($payload, 'approval.change_summary'),
            'is_fast_track' => (bool) data_get($payload, 'approval.is_fast_track', false),
            'valid_until' => filled(data_get($payload, 'approval.valid_until'))
                ? (string) data_get($payload, 'approval.valid_until')
                : null,
            'metadata' => ['custom_admin_console' => true, 'module' => 'programs'],
        ]);

        Gate::forUser($actor)->authorize('submit', $approval);

        return $this->workflow->submitForReview(
            $approval,
            $actor,
            (string) data_get($payload, 'approval.change_summary'),
        );
    }

    private function nextRevisionNumber(Program $program): int
    {
        return ((int) $program->approvalRecords()->max('revision_number')) + 1;
    }
}
