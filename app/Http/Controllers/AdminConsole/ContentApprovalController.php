<?php

namespace App\Http\Controllers\AdminConsole;

use App\Enums\ContentApprovalStatus;
use App\Enums\ContentSnapshotType;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminConsole\ContentApprovalResource;
use App\Models\ContentApproval;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\ServiceGuide;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\ContentGovernanceService;
use App\Services\ContentSnapshotService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ContentApprovalController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', ContentApproval::class);

        /** @var User $user */
        $user = $request->user();
        $allowedTypes = $this->allowedTypes($user);
        abort_if($allowedTypes === [], 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:120'],
            'owner' => ['nullable', 'string', 'max:40'],
            'attention' => ['nullable', 'in:fast_track,unassigned,overdue,due_soon'],
            'sort' => ['nullable', 'in:newest,oldest,revision,priority'],
        ]);

        $query = $this->approvalQuery($allowedTypes);

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']), $allowedTypes);
        }

        if (($status = ContentApprovalStatus::tryFrom((string) ($filters['status'] ?? ''))) !== null) {
            $query->where('status', $status->value);
        }

        if (in_array($filters['type'] ?? null, $allowedTypes, true)) {
            $query->where('approvable_type', $filters['type']);
        }

        $this->applyOwnershipFilter($query, (string) ($filters['owner'] ?? ''), $user);
        $this->applyAttentionFilter($query, (string) ($filters['attention'] ?? ''), $allowedTypes);
        $this->applySort($query, (string) ($filters['sort'] ?? 'priority'));

        return Inertia::render('Admin/ContentApprovals/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'approvals' => ContentApprovalResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'type' => $filters['type'] ?? '',
                'owner' => $filters['owner'] ?? '',
                'attention' => $filters['attention'] ?? '',
                'sort' => $filters['sort'] ?? 'priority',
            ],
            'statusOptions' => ContentApprovalStatus::options(),
            'typeOptions' => collect($allowedTypes)
                ->mapWithKeys(fn (string $class): array => [$class => str(class_basename($class))->headline()->toString()])
                ->all(),
            'reviewerOptions' => $this->reviewerOptions(),
            'queueSummary' => $this->queueSummary($allowedTypes),
        ]);
    }

    public function show(
        Request $request,
        ContentApproval $contentApproval,
        AdminConsoleNavigationService $navigation,
        ContentGovernanceService $governance,
        ContentSnapshotService $snapshots,
    ): Response {
        Gate::authorize('view', $contentApproval);
        $contentApproval->load([
            'approvable.contentOwner',
            'submitter:id,name',
            'reviewer:id,name',
            'snapshots.capturedBy:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();
        $issues = $governance->blockingIssues($contentApproval);
        $comparison = $snapshots->compareCurrentToPublished($contentApproval);

        return Inertia::render('Admin/ContentApprovals/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'approval' => (new ContentApprovalResource($contentApproval))->resolve($request),
            'comparison' => collect($comparison)->map(fn (array $change, string $field): array => [
                'field' => $field,
                'label' => str($field)->replace('_', ' ')->headline()->toString(),
                'published' => $change['from'] ?? null,
                'current' => $change['to'] ?? null,
            ])->values(),
            'snapshots' => $contentApproval->snapshots->map(fn ($snapshot): array => [
                'id' => $snapshot->id,
                'versionNumber' => $snapshot->version_number,
                'type' => [
                    'value' => $snapshot->snapshot_type->value,
                    'label' => $snapshot->snapshot_type->label(),
                ],
                'capturedBy' => $snapshot->capturedBy?->name ?? 'System',
                'capturedAt' => $snapshot->captured_at?->timezone('Asia/Manila')->format('M j, Y g:i A'),
                'changeSummary' => $snapshot->change_summary,
                'isPublishable' => $snapshot->is_publishable,
                'governanceIssues' => $snapshot->governance_issues ?? [],
                'diffCount' => count($snapshot->diff_from_previous ?? []),
                'canRollback' => $user->can('rollback', $contentApproval)
                    && $snapshot->is_publishable
                    && in_array($snapshot->snapshot_type, [ContentSnapshotType::Approved, ContentSnapshotType::Published], true),
            ])->values(),
            'workflow' => $this->workflowContext($contentApproval, $user, $issues),
            'reviewerOptions' => $this->reviewerOptions(),
            'archiveReasons' => $this->archiveReasons(),
        ]);
    }

    /** @param list<class-string> $allowedTypes */
    private function approvalQuery(array $allowedTypes): Builder
    {
        return ContentApproval::query()
            ->with([
                'approvable' => fn (MorphTo $morphTo): MorphTo => $morphTo->morphWith(
                    collect($allowedTypes)->mapWithKeys(fn (string $type): array => [$type => ['contentOwner']])->all(),
                ),
                'submitter:id,name',
                'reviewer:id,name',
            ])
            ->whereIn('approvable_type', $allowedTypes);
    }

    /** @param list<class-string> $allowedTypes */
    private function applySearch(Builder $query, string $search, array $allowedTypes): void
    {
        $fieldMap = [
            PublicPage::class => ['title', 'slug'],
            Program::class => ['title', 'slug'],
            ServiceGuide::class => ['title', 'slug'],
            LegislativeRecord::class => ['official_title', 'public_title', 'bill_number'],
            Project::class => ['title', 'slug'],
            NewsRecord::class => ['title', 'slug'],
        ];

        $query->where(function (Builder $builder) use ($search, $allowedTypes, $fieldMap): void {
            $builder->where('change_summary', 'like', '%'.$search.'%');

            if (ctype_digit($search)) {
                $builder->orWhereKey((int) $search);
            }

            $searchableTypes = array_values(array_intersect(array_keys($fieldMap), $allowedTypes));

            if ($searchableTypes !== []) {
                $builder->orWhereHasMorph(
                    'approvable',
                    $searchableTypes,
                    function (Builder $related, string $type) use ($fieldMap, $search): void {
                        $related->where(function (Builder $fields) use ($fieldMap, $search, $type): void {
                            foreach ($fieldMap[$type] ?? [] as $index => $field) {
                                $method = $index === 0 ? 'where' : 'orWhere';
                                $fields->{$method}($field, 'like', '%'.$search.'%');
                            }
                        });
                    },
                );
            }
        });
    }

    private function applyOwnershipFilter(Builder $query, string $owner, User $user): void
    {
        if ($owner === 'mine') {
            $query->where('reviewed_by_id', $user->id);
        } elseif ($owner === 'unassigned') {
            $query->whereNull('reviewed_by_id');
        } elseif (ctype_digit($owner)) {
            $query->where('reviewed_by_id', (int) $owner);
        }
    }

    /** @param list<class-string> $allowedTypes */
    private function applyAttentionFilter(Builder $query, string $attention, array $allowedTypes): void
    {
        match ($attention) {
            'fast_track' => $query->where('is_fast_track', true),
            'unassigned' => $query
                ->where('status', ContentApprovalStatus::ForReview->value)
                ->whereNull('reviewed_by_id'),
            'overdue' => $query->where(function (Builder $builder) use ($allowedTypes): void {
                $builder->where('valid_until', '<=', now())
                    ->orWhereHasMorph('approvable', $allowedTypes, fn (Builder $related): Builder => $related
                        ->where('review_due_at', '<=', now()));
            }),
            'due_soon' => $query->whereHasMorph('approvable', $allowedTypes, fn (Builder $related): Builder => $related
                ->whereBetween('review_due_at', [now(), now()->addDays(
                    (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
                )])),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->oldest('submitted_at'),
            'revision' => $query->latest('revision_number')->latest('submitted_at'),
            'newest' => $query->latest('submitted_at'),
            default => $query
                ->orderByDesc('is_fast_track')
                ->orderByRaw('CASE WHEN status = ? AND reviewed_by_id IS NULL THEN 0 ELSE 1 END', [ContentApprovalStatus::ForReview->value])
                ->oldest('submitted_at'),
        };
    }

    /** @param list<class-string> $allowedTypes
     * @return array<string, int>
     */
    private function queueSummary(array $allowedTypes): array
    {
        $base = ContentApproval::query()->whereIn('approvable_type', $allowedTypes);

        return [
            'awaitingReview' => (clone $base)->where('status', ContentApprovalStatus::ForReview->value)->count(),
            'unassigned' => (clone $base)
                ->where('status', ContentApprovalStatus::ForReview->value)
                ->whereNull('reviewed_by_id')
                ->count(),
            'fastTrack' => (clone $base)->where('is_fast_track', true)->count(),
            'staleOrExpired' => (clone $base)->where(function (Builder $query) use ($allowedTypes): void {
                $query->where('valid_until', '<=', now())
                    ->orWhereHasMorph('approvable', $allowedTypes, fn (Builder $related): Builder => $related
                        ->where('review_due_at', '<=', now()));
            })->count(),
        ];
    }

    /** @param list<string> $issues
     * @return array<string, mixed>
     */
    private function workflowContext(ContentApproval $approval, User $user, array $issues): array
    {
        $status = $approval->status;
        $action = fn (string $ability, string $route, bool $stateAllowed, bool $blocked = false): array => [
            'allowed' => $stateAllowed && $user->can($ability, $approval),
            'blocked' => $blocked,
            'url' => route($route, $approval),
        ];

        return [
            'currentUserId' => $user->id,
            'writable' => collect([
                'assignReviewer', 'submit', 'approve', 'requestChanges', 'reject', 'cancel',
                'schedule', 'publish', 'unpublish', 'archive', 'rollback',
            ])->contains(fn (string $ability): bool => $user->can($ability, $approval)),
            'publishBlocked' => $issues !== [],
            'actions' => [
                'assignReviewer' => $action('assignReviewer', 'admin-console.content-approvals.reviewer.assign', in_array($status, [
                    ContentApprovalStatus::Draft,
                    ContentApprovalStatus::ForReview,
                    ContentApprovalStatus::NeedsRevision,
                    ContentApprovalStatus::Approved,
                    ContentApprovalStatus::Scheduled,
                ], true)),
                'submit' => $action('submit', 'admin-console.content-approvals.submit', in_array($status, [
                    ContentApprovalStatus::Draft,
                    ContentApprovalStatus::NeedsRevision,
                ], true)),
                'approve' => $action('approve', 'admin-console.content-approvals.approve', $status === ContentApprovalStatus::ForReview),
                'requestChanges' => $action('requestChanges', 'admin-console.content-approvals.request-changes', $status === ContentApprovalStatus::ForReview),
                'reject' => $action('reject', 'admin-console.content-approvals.reject', $status === ContentApprovalStatus::ForReview),
                'cancel' => $action('cancel', 'admin-console.content-approvals.cancel', in_array($status, [
                    ContentApprovalStatus::ForReview,
                    ContentApprovalStatus::Scheduled,
                ], true)),
                'schedule' => $action('schedule', 'admin-console.content-approvals.schedule', in_array($status, [
                    ContentApprovalStatus::Approved,
                    ContentApprovalStatus::Scheduled,
                ], true), $issues !== []),
                'publish' => $action('publish', 'admin-console.content-approvals.publish', in_array($status, [
                    ContentApprovalStatus::Approved,
                    ContentApprovalStatus::Scheduled,
                ], true), $issues !== []),
                'unpublish' => $action('unpublish', 'admin-console.content-approvals.unpublish', $status === ContentApprovalStatus::Published),
                'archive' => $action('archive', 'admin-console.content-approvals.archive', $status !== ContentApprovalStatus::Archived),
                'rollback' => $action('rollback', 'admin-console.content-approvals.rollback', $approval->snapshots->contains(
                    fn ($snapshot): bool => $snapshot->is_publishable && in_array($snapshot->snapshot_type, [
                        ContentSnapshotType::Approved,
                        ContentSnapshotType::Published,
                    ], true),
                )),
            ],
        ];
    }

    /** @return list<array{id: int, name: string, role: string}> */
    private function reviewerOptions(): array
    {
        $roles = config('belzamora.governance.workflow_roles.review', []);

        return User::query()
            ->where('is_active', true)
            ->whereIn('admin_role', is_array($roles) ? $roles : [])
            ->orderBy('name')
            ->get(['id', 'name', 'admin_role'])
            ->map(fn (User $reviewer): array => [
                'id' => $reviewer->id,
                'name' => $reviewer->name,
                'role' => $reviewer->admin_role?->label() ?? 'Reviewer',
            ])
            ->values()
            ->all();
    }

    /** @return list<array{value: string, label: string}> */
    private function archiveReasons(): array
    {
        $reasons = config('belzamora.governance.content_approval.archive_reasons', []);

        return collect(is_array($reasons) ? $reasons : [])
            ->map(fn (string $label, string $value): array => compact('value', 'label'))
            ->values()
            ->all();
    }

    /** @return list<class-string> */
    private function allowedTypes(User $user): array
    {
        return collect([
            PublicPage::class,
            Program::class,
            ServiceGuide::class,
            LegislativeRecord::class,
            Project::class,
            NewsRecord::class,
        ])->filter(fn (string $class): bool => $user->can('viewAny', $class))->values()->all();
    }
}
