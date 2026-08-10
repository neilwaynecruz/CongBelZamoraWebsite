<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Projects\SaveProjectRequest;
use App\Http\Resources\AdminConsole\ProjectFormResource;
use App\Http\Resources\AdminConsole\ProjectResource;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectStatus;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminProjectEditorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', Project::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'integer', 'exists:project_statuses,id'],
            'category' => ['nullable', 'integer', 'exists:project_categories,id'],
            'barangay' => ['nullable', 'string', 'max:80'],
            'review' => ['nullable', 'in:due,missing_source,unpublished,evidence_pending'],
            'sort' => ['nullable', 'in:priority,title,review_due,newest'],
        ]);

        $query = Project::query()
            ->with([
                'category:id,name',
                'status:id,label,value,tone',
                'primaryPartner:id,name',
                'contentOwner:id,name',
                'approver:id,name',
                'evidences.verifier:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ])
            ->withCount(['evidences', 'sourceRecords', 'approvalRecords']);

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('project_status_id', (int) $filters['status']);
        }

        if (filled($filters['category'] ?? null)) {
            $query->where('project_category_id', (int) $filters['category']);
        }

        if (filled($filters['barangay'] ?? null)) {
            $query->where('barangay', (string) $filters['barangay']);
        }

        $this->applyReviewFilter($query, (string) ($filters['review'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'priority'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Projects/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'projects' => ProjectResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => (string) ($filters['status'] ?? ''),
                'category' => (string) ($filters['category'] ?? ''),
                'barangay' => $filters['barangay'] ?? '',
                'review' => $filters['review'] ?? '',
                'sort' => $filters['sort'] ?? 'priority',
            ],
            'statusOptions' => $this->statusOptions(),
            'categoryOptions' => $this->categoryOptions(),
            'barangayOptions' => $this->barangayOptions(),
            'summary' => $this->summary(),
            'can' => [
                'create' => $user->can('create', Project::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', Project::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Projects/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new ProjectFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SaveProjectRequest $request,
        AdminProjectEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $project = $editor->create($request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Project record created in the custom command system.',
        ]);

        return redirect()->route('admin-console.projects.show', $project);
    }

    public function show(
        Request $request,
        string $project,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $project = Project::withTrashed()->findOrFail($project);

        Gate::authorize('view', $project);

        $project->load([
            'category:id,name',
            'status:id,label,value,tone',
            'primaryPartner:id,name',
            'contentOwner:id,name',
            'approver:id,name',
            'evidences.verifier:id,name',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Projects/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'project' => (new ProjectResource($project))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($project, $user),
        ]);
    }

    public function edit(
        Request $request,
        Project $project,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $project);

        $project->load([
            'evidences',
            'sourceRecords',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Projects/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new ProjectFormResource($project, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveProjectRequest $request,
        Project $project,
        AdminProjectEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $editor->update($project, $request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Project record updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.projects.show', $project);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')
                ->orWhere('summary', 'like', '%'.$search.'%')
                ->orWhere('barangay', 'like', '%'.$search.'%')
                ->orWhere('location', 'like', '%'.$search.'%')
                ->orWhere('partner_label', 'like', '%'.$search.'%')
                ->orWhere('responsible_office', 'like', '%'.$search.'%');
        });
    }

    private function applyReviewFilter(Builder $query, string $review): void
    {
        match ($review) {
            'due' => $query->whereNotNull('review_due_at')->where('review_due_at', '<=', now()->addDays(
                (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
            )),
            'missing_source' => $query->where(function (Builder $builder): void {
                $builder->whereNull('source_url')->orWhereNull('verified_at');
            }),
            'unpublished' => $query->whereNull('published_at'),
            'evidence_pending' => $query->whereDoesntHave('evidences', function (Builder $builder): void {
                $builder
                    ->whereNotNull('caption')
                    ->whereNotNull('alt_text')
                    ->whereNotNull('rights_note')
                    ->whereNotNull('verified_at');
            }),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('title'),
            'review_due' => $query->orderByRaw('review_due_at IS NULL')->orderBy('review_due_at')->orderBy('title'),
            'newest' => $query->latest('created_at'),
            default => $query
                ->orderByRaw('CASE WHEN review_due_at IS NOT NULL AND review_due_at <= ? THEN 0 ELSE 1 END', [
                    now()->addDays((int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7)),
                ])
                ->orderByRaw('published_at IS NULL DESC')
                ->orderBy('sort_order')
                ->orderBy('title'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return ProjectCategory::query()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (ProjectCategory $category): array => [
                'value' => (string) $category->id,
                'label' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function statusOptions(): array
    {
        return ProjectStatus::query()
            ->ordered()
            ->get(['id', 'label'])
            ->map(fn (ProjectStatus $status): array => [
                'value' => (string) $status->id,
                'label' => $status->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function barangayOptions(): array
    {
        return Project::query()
            ->select('barangay')
            ->distinct()
            ->orderBy('barangay')
            ->pluck('barangay')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = Project::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->published()->count(),
            'reviewDue' => (clone $base)
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now()->addDays(
                    (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
                ))
                ->count(),
            'evidencePending' => (clone $base)
                ->whereDoesntHave('evidences', function (Builder $query): void {
                    $query
                        ->whereNotNull('caption')
                        ->whereNotNull('alt_text')
                        ->whereNotNull('rights_note')
                        ->whereNotNull('verified_at');
                })
                ->count(),
        ];
    }

    private function filamentFallbackUrl(Project $project, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->can('update', $project)) {
            return null;
        }

        return \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $project]);
    }
}
