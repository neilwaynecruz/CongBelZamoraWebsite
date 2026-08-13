<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\News\SaveNewsRecordRequest;
use App\Http\Resources\AdminConsole\NewsRecordFormResource;
use App\Http\Resources\AdminConsole\NewsRecordResource;
use App\Models\NewsCategory;
use App\Models\NewsRecord;
use App\Models\NewsStatus;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminNewsRecordEditorService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NewsRecordController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', NewsRecord::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'integer', 'exists:news_statuses,id'],
            'category' => ['nullable', 'integer', 'exists:news_categories,id'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'review' => ['nullable', 'in:due,missing_source,unpublished,media_pending,stale_status'],
            'sort' => ['nullable', 'in:priority,title,review_due,newest,published'],
        ]);

        $query = NewsRecord::query()
            ->with([
                'category:id,name',
                'status:id,label,value,tone',
                'contentOwner:id,name',
                'approver:id,name',
                'mediaAssets.verifier:id,name',
                'sourceRecords.verifier:id,name',
                'approvalRecords.reviewer:id,name',
            ])
            ->withCount(['mediaAssets', 'sourceRecords', 'approvalRecords']);

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (filled($filters['status'] ?? null)) {
            $query->where('news_status_id', (int) $filters['status']);
        }

        if (filled($filters['category'] ?? null)) {
            $query->where('news_category_id', (int) $filters['category']);
        }

        if (filled($filters['year'] ?? null)) {
            $query->whereYear('published_at', (int) $filters['year']);
        }

        $this->applyReviewFilter($query, (string) ($filters['review'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'priority'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/News/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'newsRecords' => NewsRecordResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => (string) ($filters['status'] ?? ''),
                'category' => (string) ($filters['category'] ?? ''),
                'year' => (string) ($filters['year'] ?? ''),
                'review' => $filters['review'] ?? '',
                'sort' => $filters['sort'] ?? 'priority',
            ],
            'statusOptions' => $this->statusOptions(),
            'categoryOptions' => $this->categoryOptions(),
            'yearOptions' => $this->yearOptions(),
            'summary' => $this->summary(),
            'can' => [
                'create' => $user->can('create', NewsRecord::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', NewsRecord::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/News/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new NewsRecordFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SaveNewsRecordRequest $request,
        AdminNewsRecordEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $record = $editor->create($request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'News/media record created in the custom command system.',
        ]);

        return redirect()->route('admin-console.news.show', $record);
    }

    public function show(
        Request $request,
        string $newsRecord,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $newsRecord = NewsRecord::withTrashed()->findOrFail($newsRecord);

        Gate::authorize('view', $newsRecord);

        $newsRecord->load([
            'category:id,name',
            'status:id,label,value,tone',
            'contentOwner:id,name',
            'approver:id,name',
            'mediaAssets.verifier:id,name',
            'sourceRecords.verifier:id,name',
            'approvalRecords.reviewer:id,name',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/News/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'newsRecord' => (new NewsRecordResource($newsRecord))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($newsRecord, $user),
        ]);
    }

    public function edit(
        Request $request,
        NewsRecord $newsRecord,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $newsRecord);

        $newsRecord->load([
            'mediaAssets',
            'sourceRecords',
        ]);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/News/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new NewsRecordFormResource($newsRecord, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveNewsRecordRequest $request,
        NewsRecord $newsRecord,
        AdminNewsRecordEditorService $editor,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $editor->update($newsRecord, $request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'News/media record updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.news.show', $newsRecord);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')
                ->orWhere('summary', 'like', '%'.$search.'%')
                ->orWhere('official_update', 'like', '%'.$search.'%')
                ->orWhere('owner_label', 'like', '%'.$search.'%')
                ->orWhere('source_label', 'like', '%'.$search.'%');
        });
    }

    private function applyReviewFilter(Builder $query, string $review): void
    {
        match ($review) {
            'due' => $query->whereNotNull('review_due_at')->where('review_due_at', '<=', now()->addDays(
                (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
            )),
            'missing_source' => $query->where(function (Builder $builder): void {
                $builder->whereNull('source_url')->orWhereNull('source_verified_at');
            }),
            'unpublished' => $query->whereNull('published_at'),
            'media_pending' => $query->whereDoesntHave('mediaAssets', function (Builder $builder): void {
                $builder
                    ->whereNotNull('media_path')
                    ->where('media_path', '!=', '')
                    ->whereNotNull('caption')
                    ->whereNotNull('alt_text')
                    ->whereNotNull('rights_note')
                    ->whereNotNull('verified_at');
            }),
            'stale_status' => $query->whereHas('status', function (Builder $builder): void {
                $builder->where('value', 'stale_review');
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
            'published' => $query->orderByDesc('published_at')->orderBy('title'),
            default => $query
                ->orderByRaw('CASE WHEN review_due_at IS NOT NULL AND review_due_at <= ? THEN 0 ELSE 1 END', [
                    now()->addDays((int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7)),
                ])
                ->orderByRaw('published_at IS NULL DESC')
                ->orderBy('sort_order')
                ->orderByDesc('published_at'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return NewsCategory::query()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn (NewsCategory $category): array => [
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
        return NewsStatus::query()
            ->ordered()
            ->get(['id', 'label'])
            ->map(fn (NewsStatus $status): array => [
                'value' => (string) $status->id,
                'label' => $status->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private function yearOptions(): array
    {
        return NewsRecord::query()
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->get(['published_at'])
            ->map(fn (NewsRecord $record): ?string => $record->published_at?->setTimezone('Asia/Manila')->format('Y'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = NewsRecord::query();

        return [
            'total' => (clone $base)->count(),
            'published' => (clone $base)->published()->count(),
            'reviewDue' => (clone $base)
                ->whereNotNull('review_due_at')
                ->where('review_due_at', '<=', now()->addDays(
                    (int) config('belzamora.governance.content_approval.stale_warning_days_before_review_due', 7),
                ))
                ->count(),
            'mediaPending' => (clone $base)
                ->whereDoesntHave('mediaAssets', function (Builder $query): void {
                    $query
                        ->whereNotNull('media_path')
                        ->where('media_path', '!=', '')
                        ->whereNotNull('caption')
                        ->whereNotNull('alt_text')
                        ->whereNotNull('rights_note')
                        ->whereNotNull('verified_at');
                })
                ->count(),
        ];
    }

    private function filamentFallbackUrl(NewsRecord $record, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->canAccessFilamentFallback() || ! $user->can('update', $record)) {
            return null;
        }

        return \App\Filament\Resources\NewsRecordResource::getUrl('edit', ['record' => $record]);
    }
}
