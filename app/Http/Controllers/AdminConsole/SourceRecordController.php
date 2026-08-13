<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\SourceRecords\SaveSourceRecordRequest;
use App\Http\Resources\AdminConsole\SourceRecordFormResource;
use App\Http\Resources\AdminConsole\SourceRecordResource;
use App\Models\SourceRecord;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminSourceRecordService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SourceRecordController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', SourceRecord::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', 'in:primary_official,reliable_secondary,discovery_only,internal_approval'],
            'tier' => ['nullable', 'in:tier_1,tier_2,tier_3'],
            'review' => ['nullable', 'in:due,expired,unverified,archived'],
            'sort' => ['nullable', 'in:review_due,title,newest,verified,expires'],
        ]);

        $query = SourceRecord::query()
            ->with(['contentOwner:id,name', 'verifier:id,name', 'sourceable']);

        if (($filters['review'] ?? null) === 'archived') {
            $query->onlyTrashed();
        }

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (filled($filters['type'] ?? null)) {
            $query->where('source_type', (string) $filters['type']);
        }

        if (filled($filters['tier'] ?? null)) {
            $query->where('source_tier', (string) $filters['tier']);
        }

        $this->applyReviewFilter($query, (string) ($filters['review'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'review_due'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/SourceRecords/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'sourceRecords' => SourceRecordResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'type' => $filters['type'] ?? '',
                'tier' => $filters['tier'] ?? '',
                'review' => $filters['review'] ?? '',
                'sort' => $filters['sort'] ?? 'review_due',
            ],
            'summary' => $this->summary(),
            'can' => [
                'create' => $user->can('create', SourceRecord::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', SourceRecord::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/SourceRecords/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new SourceRecordFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(SaveSourceRecordRequest $request, AdminSourceRecordService $sourceRecords): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $sourceRecord = $sourceRecords->create($request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Source record created in the custom command system.',
        ]);

        return redirect()->route('admin-console.source-records.show', $sourceRecord);
    }

    public function show(
        Request $request,
        string $sourceRecord,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $sourceRecord = SourceRecord::withTrashed()
            ->with(['contentOwner:id,name', 'verifier:id,name', 'sourceable'])
            ->findOrFail($sourceRecord);

        Gate::authorize('view', $sourceRecord);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/SourceRecords/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'sourceRecord' => (new SourceRecordResource($sourceRecord))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($sourceRecord, $user),
        ]);
    }

    public function edit(
        Request $request,
        SourceRecord $sourceRecord,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $sourceRecord);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/SourceRecords/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new SourceRecordFormResource($sourceRecord, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveSourceRecordRequest $request,
        SourceRecord $sourceRecord,
        AdminSourceRecordService $sourceRecords,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        $sourceRecords->update($sourceRecord, $request->payload(), $user);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Source record updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.source-records.show', $sourceRecord);
    }

    public function archive(
        Request $request,
        SourceRecord $sourceRecord,
        AdminSourceRecordService $sourceRecords,
    ): RedirectResponse {
        Gate::authorize('delete', $sourceRecord);

        /** @var User $user */
        $user = $request->user();

        $sourceRecords->archive($sourceRecord, $user);

        return redirect()->route('admin-console.source-records.show', $sourceRecord);
    }

    public function restore(
        Request $request,
        string $sourceRecord,
        AdminSourceRecordService $sourceRecords,
    ): RedirectResponse {
        $sourceRecord = SourceRecord::withTrashed()->findOrFail($sourceRecord);

        Gate::authorize('restore', $sourceRecord);

        /** @var User $user */
        $user = $request->user();

        $sourceRecords->restore($sourceRecord, $user);

        return redirect()->route('admin-console.source-records.show', $sourceRecord);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('url', 'like', '%'.$search.'%')
                ->orWhere('description', 'like', '%'.$search.'%')
                ->orWhere('public_status', 'like', '%'.$search.'%');
        });
    }

    private function applyReviewFilter(Builder $query, string $review): void
    {
        match ($review) {
            'due' => $query->whereNotNull('next_review_at')->where('next_review_at', '<=', now()),
            'expired' => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()),
            'unverified' => $query->whereNull('verified_at'),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'title' => $query->orderBy('title'),
            'newest' => $query->latest('created_at'),
            'verified' => $query->orderByRaw('verified_at IS NULL')->orderByDesc('verified_at'),
            'expires' => $query->orderByRaw('expires_at IS NULL')->orderBy('expires_at'),
            default => $query->orderByRaw('next_review_at IS NULL')->orderBy('next_review_at')->orderBy('title'),
        };
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = SourceRecord::query();

        return [
            'total' => (clone $base)->count(),
            'due' => (clone $base)->whereNotNull('next_review_at')->where('next_review_at', '<=', now())->count(),
            'expired' => (clone $base)->whereNotNull('expires_at')->where('expires_at', '<=', now())->count(),
            'archived' => SourceRecord::onlyTrashed()->count(),
        ];
    }

    private function filamentFallbackUrl(SourceRecord $sourceRecord, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->can('update', $sourceRecord)) {
            return null;
        }

        return \App\Filament\Resources\SourceRecordResource::getUrl('index');
    }
}
