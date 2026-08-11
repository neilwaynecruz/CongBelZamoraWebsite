<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Legislation\SaveLegislativeRecordRequest;
use App\Http\Resources\AdminConsole\LegislativeRecordFormResource;
use App\Http\Resources\AdminConsole\LegislativeRecordResource;
use App\Models\LegislativeRecord;
use App\Models\LegislativeRole;
use App\Models\LegislativeStatus;
use App\Models\LegislativeTopic;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminLegislativeRecordEditorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LegislativeRecordController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', LegislativeRecord::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', 'integer', 'exists:legislative_roles,id'],
            'status' => ['nullable', 'integer', 'exists:legislative_statuses,id'],
            'topic' => ['nullable', 'integer', 'exists:legislative_topics,id'],
            'review' => ['nullable', 'in:due,missing_source,unpublished,source_history_pending'],
            'sort' => ['nullable', 'in:priority,title,review_due,newest,filed'],
        ]);

        $query = LegislativeRecord::query()
            ->with([
                'role:id,label,value',
                'status:id,label,tone',
                'topic:id,name',
                'contentOwner:id,name',
                'approver:id,name',
                'sourceHistories.checker:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ])
            ->withCount(['sourceHistories', 'sourceRecords', 'approvalRecords']);

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (filled($filters['role'] ?? null)) {
            $query->where('legislative_role_id', (int) $filters['role']);
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('legislative_status_id', (int) $filters['status']);
        }

        if (filled($filters['topic'] ?? null)) {
            $query->where('legislative_topic_id', (int) $filters['topic']);
        }

        $this->applyReviewFilter($query, (string) ($filters['review'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'priority'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Legislation/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'legislativeRecords' => LegislativeRecordResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => (string) ($filters['role'] ?? ''),
                'status' => (string) ($filters['status'] ?? ''),
                'topic' => (string) ($filters['topic'] ?? ''),
                'review' => $filters['review'] ?? '',
                'sort' => $filters['sort'] ?? 'priority',
            ],
            'roleOptions' => $this->roleOptions(),
            'statusOptions' => $this->statusOptions(),
            'topicOptions' => $this->topicOptions(),
            'summary' => $this->summary(),
            'can' => [
                'create' => $user->can('create', LegislativeRecord::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', LegislativeRecord::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Legislation/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new LegislativeRecordFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SaveLegislativeRecordRequest $request,
        AdminLegislativeRecordEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $record = $editor->create($request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Legislative record created in the custom command system.',
        ]);

        return redirect()->route('admin-console.legislation.show', $record);
    }

    public function show(
        Request $request,
        string $legislativeRecord,
        AdminConsoleNavigationService $navigation,
        AdminLegislativeRecordEditorService $editor,
    ): Response {
        $record = LegislativeRecord::withTrashed()->findOrFail($legislativeRecord);

        Gate::authorize('view', $record);

        $record->load($editor->relations());

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Legislation/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'legislativeRecord' => (new LegislativeRecordResource($record))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($record, $user),
        ]);
    }

    public function edit(
        Request $request,
        LegislativeRecord $legislativeRecord,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $legislativeRecord);

        $legislativeRecord->load([
            'sourceHistories',
            'sourceRecords',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Legislation/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new LegislativeRecordFormResource($legislativeRecord, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveLegislativeRecordRequest $request,
        LegislativeRecord $legislativeRecord,
        AdminLegislativeRecordEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $editor->update($legislativeRecord, $request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Legislative record updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.legislation.show', $legislativeRecord);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('bill_number', 'like', '%'.$search.'%')
                ->orWhere('public_title', 'like', '%'.$search.'%')
                ->orWhere('official_title', 'like', '%'.$search.'%')
                ->orWhere('plain_language_summary', 'like', '%'.$search.'%')
                ->orWhere('committee', 'like', '%'.$search.'%')
                ->orWhere('official_source_label', 'like', '%'.$search.'%');
        });
    }

    private function applyReviewFilter(Builder $query, string $review): void
    {
        match ($review) {
            'due' => $query->whereNotNull('review_due_at')->where('review_due_at', '<=', now()->addDays(
                (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
            )),
            'missing_source' => $query->where(function (Builder $builder): void {
                $builder->whereNull('official_source_url')->orWhereNull('verified_at');
            }),
            'unpublished' => $query->whereNull('published_at'),
            'source_history_pending' => $query->doesntHave('sourceHistories'),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('public_title'),
            'review_due' => $query->orderByRaw('review_due_at IS NULL')->orderBy('review_due_at')->orderBy('public_title'),
            'newest' => $query->latest('created_at'),
            'filed' => $query->orderByDesc('filed_at')->orderBy('bill_number'),
            default => $query
                ->orderByRaw('CASE WHEN review_due_at IS NOT NULL AND review_due_at <= ? THEN 0 ELSE 1 END', [
                    now()->addDays((int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7)),
                ])
                ->orderByRaw('published_at IS NULL DESC')
                ->orderBy('sort_order')
                ->orderBy('bill_number'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return LegislativeRole::query()
            ->active()
            ->ordered()
            ->get(['id', 'label'])
            ->map(fn (LegislativeRole $role): array => [
                'value' => (string) $role->id,
                'label' => $role->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return LegislativeStatus::query()
            ->active()
            ->ordered()
            ->get(['id', 'label'])
            ->map(fn (LegislativeStatus $status): array => [
                'value' => (string) $status->id,
                'label' => $status->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function topicOptions(): array
    {
        return LegislativeTopic::query()
            ->active()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (LegislativeTopic $topic): array => [
                'value' => (string) $topic->id,
                'label' => $topic->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = LegislativeRecord::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->published()->count(),
            'reviewDue' => (clone $base)
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now()->addDays(
                    (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
                ))
                ->count(),
            'sourceHistoryPending' => (clone $base)->doesntHave('sourceHistories')->count(),
        ];
    }

    private function filamentFallbackUrl(LegislativeRecord $record, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->can('update', $record)) {
            return null;
        }

        return \App\Filament\Resources\LegislativeRecordResource::getUrl('edit', ['record' => $record]);
    }
}
