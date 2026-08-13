<?php

namespace App\Http\Controllers\AdminConsole;

use App\Enums\ProgramStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Programs\SaveProgramRequest;
use App\Http\Resources\AdminConsole\ProgramFormResource;
use App\Http\Resources\AdminConsole\ProgramResource;
use App\Models\Program;
use App\Models\ProgramCategory;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminProgramEditorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProgramController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', Program::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string'],
            'category' => ['nullable', 'integer', 'exists:program_categories,id'],
            'review' => ['nullable', 'in:due,missing_source,unpublished,featured'],
            'sort' => ['nullable', 'in:priority,title,review_due,newest'],
        ]);

        $query = Program::query()
            ->with([
                'category:id,name',
                'contentOwner:id,name',
                'approver:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ])
            ->withCount(['requirements', 'updates', 'sourceRecords', 'approvalRecords']);

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (($status = ProgramStatus::tryFrom((string) ($filters['status'] ?? ''))) !== null) {
            $query->where('status', $status->value);
        }

        if (filled($filters['category'] ?? null)) {
            $query->where('program_category_id', (int) $filters['category']);
        }

        $this->applyReviewFilter($query, (string) ($filters['review'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'priority'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Programs/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'programs' => ProgramResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'category' => (string) ($filters['category'] ?? ''),
                'review' => $filters['review'] ?? '',
                'sort' => $filters['sort'] ?? 'priority',
            ],
            'statusOptions' => ProgramStatus::options(),
            'categoryOptions' => $this->categoryOptions(),
            'summary' => $this->summary(),
            'can' => [
                'create' => $user->can('create', Program::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', Program::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Programs/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new ProgramFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SaveProgramRequest $request,
        AdminProgramEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $program = $editor->create($request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Program record created in the custom command system.',
        ]);

        return redirect()->route('admin-console.programs.show', $program);
    }

    public function show(
        Request $request,
        string $program,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $program = Program::withTrashed()->findOrFail($program);

        Gate::authorize('view', $program);

        $program->load([
            'category:id,name',
            'contentOwner:id,name',
            'approver:id,name',
            'requirements',
            'updates',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Programs/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'program' => (new ProgramResource($program))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($program, $user),
        ]);
    }

    public function edit(
        Request $request,
        Program $program,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $program);

        $program->load([
            'requirements',
            'updates',
            'sourceRecords',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Programs/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new ProgramFormResource($program, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveProgramRequest $request,
        Program $program,
        AdminProgramEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $editor->update($program, $request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Program record updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.programs.show', $program);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')
                ->orWhere('summary', 'like', '%'.$search.'%')
                ->orWhere('sector', 'like', '%'.$search.'%')
                ->orWhere('responsible_office_label', 'like', '%'.$search.'%')
                ->orWhere('partner_agency', 'like', '%'.$search.'%');
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
            'featured' => $query->where('is_featured', true),
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
     * @return list<array{id: int, name: string}>
     */
    private function categoryOptions(): array
    {
        return ProgramCategory::query()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (ProgramCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = Program::query();

        return [
            'total' => (clone $base)->count(),
            'open' => (clone $base)->where('status', ProgramStatus::Open->value)->count(),
            'reviewDue' => (clone $base)
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now()->addDays(
                    (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
                ))
                ->count(),
            'missingSource' => (clone $base)
                ->where(function (Builder $query): void {
                    $query->whereNull('source_url')->orWhereNull('verified_at');
                })
                ->count(),
        ];
    }

    private function filamentFallbackUrl(Program $program, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->canAccessFilamentFallback() || ! $user->can('update', $program)) {
            return null;
        }

        return \App\Filament\Resources\ProgramResource::getUrl('edit', ['record' => $program]);
    }
}
