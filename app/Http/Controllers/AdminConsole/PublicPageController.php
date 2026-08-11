<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\PublicPages\SavePublicPageRequest;
use App\Http\Resources\AdminConsole\PublicPageFormResource;
use App\Http\Resources\AdminConsole\PublicPageResource;
use App\Models\PublicPage;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminPublicPageEditorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', PublicPage::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:published,unpublished,archived'],
            'page_type' => ['nullable', 'in:landing,profile,service,policy,legal,contact,utility'],
            'review' => ['nullable', 'in:due,missing_source,unpublished,missing_seo'],
            'sort' => ['nullable', 'in:priority,title,slug,review_due,newest,published'],
        ]);

        $query = PublicPage::query()
            ->with([
                'contentOwner:id,name',
                'approver:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ])
            ->withCount(['sourceRecords', 'approvalRecords']);

        if (($filters['status'] ?? null) === 'archived') {
            $query->onlyTrashed();
        }

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (($filters['status'] ?? null) === 'published') {
            $query->whereNotNull('published_at');
        }

        if (($filters['status'] ?? null) === 'unpublished') {
            $query->whereNull('published_at');
        }

        if (filled($filters['page_type'] ?? null)) {
            $query->where('metadata->page_type', (string) $filters['page_type']);
        }

        $this->applyReviewFilter($query, (string) ($filters['review'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'priority'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/PublicPages/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'publicPages' => PublicPageResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'page_type' => $filters['page_type'] ?? '',
                'review' => $filters['review'] ?? '',
                'sort' => $filters['sort'] ?? 'priority',
            ],
            'pageTypeOptions' => $this->pageTypeOptions(),
            'summary' => $this->summary(),
            'can' => [
                'create' => $user->can('create', PublicPage::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', PublicPage::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/PublicPages/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new PublicPageFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SavePublicPageRequest $request,
        AdminPublicPageEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $page = $editor->create($request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Public page created in the custom command system.',
        ]);

        return redirect()->route('admin-console.public-pages.show', $page);
    }

    public function show(
        Request $request,
        string $publicPage,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $publicPage = PublicPage::withTrashed()->findOrFail($publicPage);

        Gate::authorize('view', $publicPage);

        $publicPage->load([
            'contentOwner:id,name',
            'approver:id,name',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/PublicPages/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'publicPage' => (new PublicPageResource($publicPage))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($publicPage, $user),
        ]);
    }

    public function edit(
        Request $request,
        PublicPage $publicPage,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $publicPage);

        $publicPage->load([
            'sourceRecords',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/PublicPages/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new PublicPageFormResource($publicPage, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SavePublicPageRequest $request,
        PublicPage $publicPage,
        AdminPublicPageEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $editor->update($publicPage, $request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Public page updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.public-pages.show', $publicPage);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')
                ->orWhere('component', 'like', '%'.$search.'%')
                ->orWhere('owner_label', 'like', '%'.$search.'%')
                ->orWhere('source_label', 'like', '%'.$search.'%')
                ->orWhere('source_url', 'like', '%'.$search.'%');
        });
    }

    private function applyReviewFilter(Builder $query, string $review): void
    {
        match ($review) {
            'due' => $query->whereNotNull('review_due_at')->where('review_due_at', '<=', now()->addDays(
                (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
            )),
            'missing_source' => $query->where(function (Builder $builder): void {
                $builder->whereNull('source_label')->orWhereNull('verified_at');
            }),
            'unpublished' => $query->whereNull('published_at'),
            'missing_seo' => $query->where(function (Builder $builder): void {
                $builder
                    ->whereNull('metadata->seo->title')
                    ->orWhereNull('metadata->seo->description');
            }),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('title'),
            'slug' => $query->orderBy('slug'),
            'review_due' => $query->orderByRaw('review_due_at IS NULL')->orderBy('review_due_at')->orderBy('slug'),
            'newest' => $query->latest('created_at'),
            'published' => $query->orderByDesc('published_at')->orderBy('slug'),
            default => $query
                ->orderByRaw('CASE WHEN review_due_at IS NOT NULL AND review_due_at <= ? THEN 0 ELSE 1 END', [
                    now()->addDays((int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7)),
                ])
                ->orderByRaw('published_at IS NULL DESC')
                ->orderBy('slug'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function pageTypeOptions(): array
    {
        return [
            ['value' => 'landing', 'label' => 'Landing page'],
            ['value' => 'profile', 'label' => 'Profile / biography'],
            ['value' => 'service', 'label' => 'Service information'],
            ['value' => 'policy', 'label' => 'Policy page'],
            ['value' => 'legal', 'label' => 'Legal notice'],
            ['value' => 'contact', 'label' => 'Contact page'],
            ['value' => 'utility', 'label' => 'Utility page'],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = PublicPage::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->published()->count(),
            'reviewDue' => (clone $base)
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now()->addDays(
                    (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
                ))
                ->count(),
            'archived' => PublicPage::onlyTrashed()->count(),
        ];
    }

    private function filamentFallbackUrl(PublicPage $page, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->can('update', $page)) {
            return null;
        }

        return \App\Filament\Resources\PublicPageResource::getUrl('edit', ['record' => $page]);
    }
}
