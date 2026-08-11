<?php

namespace App\Services;

use App\Enums\ContentApprovalStatus;
use App\Models\ContentApproval;
use App\Models\PublicPage;
use App\Models\SourceRecord;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdminPublicPageEditorService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
        private readonly ContentApprovalWorkflowService $workflow,
        private readonly PublicCacheInvalidator $cacheInvalidator,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload, User $actor): PublicPage
    {
        return $this->save(new PublicPage, $payload, $actor, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function update(PublicPage $page, array $payload, User $actor): PublicPage
    {
        return $this->save($page, $payload, $actor, false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function save(PublicPage $page, array $payload, User $actor, bool $creating): PublicPage
    {
        return DB::transaction(function () use ($page, $payload, $actor, $creating): PublicPage {
            $originalSlug = $page->exists ? (string) $page->getOriginal('slug') : null;
            $before = $page->exists ? Arr::only($page->getOriginal(), [
                'title',
                'slug',
                'component',
                'source_url',
                'verified_at',
                'review_due_at',
                'published_at',
            ]) : [];

            $page->fill($this->pageAttributes($payload));
            $page->content_owner_id ??= $actor->id;

            if ($creating) {
                $page->approved_by_id = null;
            }

            $page->save();

            $this->syncSourceRecord($page, $payload['source_record'] ?? [], $actor);
            if ($originalSlug !== null && $originalSlug !== $page->slug) {
                $this->cacheInvalidator->forgetStaticPage($originalSlug);
            }

            $this->cacheInvalidator->forgetStaticPage($page->slug);

            $this->auditLog->record(
                event: $creating ? 'public_page.created' : 'public_page.updated',
                auditable: $page,
                actor: $actor,
                oldValues: $before,
                newValues: [
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'component' => $page->component,
                    'source_url' => $page->source_url,
                    'verified_at' => $page->verified_at?->toIso8601String(),
                    'review_due_at' => $page->review_due_at?->toIso8601String(),
                    'published_at' => $page->published_at?->toIso8601String(),
                    'props_keys' => array_keys($page->props ?? []),
                    'route_name' => data_get($page->metadata, 'route_name'),
                    'page_type' => data_get($page->metadata, 'page_type'),
                ],
                metadata: ['custom_admin_console' => true],
            );

            if ((bool) data_get($payload, 'approval.submit_for_review', false)) {
                $this->submitForReview($page, $payload, $actor);
            }

            return $page->fresh([
                'contentOwner:id,name',
                'approver:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ]) ?? $page;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function pageAttributes(array $payload): array
    {
        return Arr::only($payload, [
            'title',
            'slug',
            'component',
            'props',
            'owner_label',
            'source_label',
            'source_url',
            'verified_at',
            'review_due_at',
            'published_at',
            'metadata',
        ]);
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function syncSourceRecord(PublicPage $page, array $source, User $actor): SourceRecord
    {
        /** @var SourceRecord|null $sourceRecord */
        $sourceRecord = $page->sourceRecords()
            ->latest('verified_at')
            ->first();

        $sourceRecord ??= $page->sourceRecords()->make();
        $sourceRecord->fill([
            'title' => (string) $source['title'],
            'url' => filled($source['url'] ?? null) ? (string) $source['url'] : ($page->source_url ?? url('/'.$page->slug)),
            'source_type' => (string) $source['source_type'],
            'source_tier' => (string) $source['source_tier'],
            'public_status' => (string) $source['public_status'],
            'description' => filled($source['description'] ?? null) ? (string) $source['description'] : null,
            'content_owner_id' => $page->content_owner_id ?? $actor->id,
            'verified_by_id' => $actor->id,
            'verified_at' => (string) $source['verified_at'],
            'last_reviewed_at' => now(),
            'next_review_at' => (string) $source['next_review_at'],
            'metadata' => ['custom_admin_console' => true, 'module' => 'public_pages'],
        ]);
        $sourceRecord->save();

        return $sourceRecord;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function submitForReview(PublicPage $page, array $payload, User $actor): ContentApproval
    {
        Gate::forUser($actor)->authorize('create', ContentApproval::class);

        $approval = $page->approvalRecords()->create([
            'status' => ContentApprovalStatus::Draft,
            'revision_number' => $this->nextRevisionNumber($page),
            'submitted_by_id' => $actor->id,
            'change_summary' => (string) data_get($payload, 'approval.change_summary'),
            'is_fast_track' => (bool) data_get($payload, 'approval.is_fast_track', false),
            'valid_until' => filled(data_get($payload, 'approval.valid_until'))
                ? (string) data_get($payload, 'approval.valid_until')
                : null,
            'metadata' => ['custom_admin_console' => true, 'module' => 'public_pages'],
        ]);

        Gate::forUser($actor)->authorize('submit', $approval);

        return $this->workflow->submitForReview(
            $approval,
            $actor,
            (string) data_get($payload, 'approval.change_summary'),
        );
    }

    private function nextRevisionNumber(PublicPage $page): int
    {
        return ((int) $page->approvalRecords()->max('revision_number')) + 1;
    }
}
