<?php

namespace App\Http\Controllers\AdminConsole;

use App\Filament\Resources\InquiryCategoryResource;
use App\Filament\Resources\LegislativeRoleResource;
use App\Filament\Resources\LegislativeStatusResource;
use App\Filament\Resources\LegislativeTopicResource;
use App\Filament\Resources\NewsCategoryResource;
use App\Filament\Resources\NewsStatusResource;
use App\Filament\Resources\ProgramCategoryResource;
use App\Filament\Resources\ProjectCategoryResource;
use App\Filament\Resources\ProjectPartnerResource;
use App\Filament\Resources\ProjectStatusResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\ReferenceData\SaveReferenceDataRequest;
use App\Http\Resources\AdminConsole\ReferenceDataFormResource;
use App\Http\Resources\AdminConsole\ReferenceDataResource;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminReferenceDataRegistry;
use App\Services\AdminReferenceDataService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ReferenceDataController extends Controller
{
    public function index(
        Request $request,
        string $type,
        AdminReferenceDataRegistry $registry,
        AdminReferenceDataService $service,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $config = $registry->get($type);
        Gate::authorize('viewAny', $config['model']);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'in:active,inactive'],
            'sort' => ['nullable', 'in:sort_order,title,newest,usage'],
        ]);

        /** @var Builder<Model> $query */
        $query = $config['model']::query()
            ->with(['sourceRecords.verifier:id,name']);

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']), $config);
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }

        if (($filters['status'] ?? null) === 'inactive') {
            $query->where('is_active', false);
        }

        $this->applySort($query, (string) ($filters['sort'] ?? 'sort_order'), $config);

        /** @var User $user */
        $user = $request->user();

        $records = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/ReferenceData/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'records' => [
                'data' => $records->getCollection()
                    ->map(fn (Model $record): array => (new ReferenceDataResource($record, $config))->resolve($request))
                    ->values()
                    ->all(),
                'links' => [
                    'first' => $records->url(1),
                    'last' => $records->url($records->lastPage()),
                    'prev' => $records->previousPageUrl(),
                    'next' => $records->nextPageUrl(),
                ],
                'meta' => [
                    'current_page' => $records->currentPage(),
                    'from' => $records->firstItem(),
                    'last_page' => $records->lastPage(),
                    'links' => $records->linkCollection()->toArray(),
                    'path' => $records->path(),
                    'per_page' => $records->perPage(),
                    'to' => $records->lastItem(),
                    'total' => $records->total(),
                ],
            ],
            'definition' => [
                'type' => $config['type'],
                'label' => $config['label'],
                'singular' => $config['singular'],
                'description' => $config['description'],
                'allTypes' => $registry->navigationOptions(),
            ],
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'sort' => $filters['sort'] ?? 'sort_order',
            ],
            'summary' => $this->summary($config, $service),
            'can' => [
                'create' => $user->can('create', $config['model']),
            ],
        ]);
    }

    public function create(
        Request $request,
        string $type,
        AdminReferenceDataRegistry $registry,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $config = $registry->get($type);
        Gate::authorize('create', $config['model']);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/ReferenceData/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new ReferenceDataFormResource($config, null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SaveReferenceDataRequest $request,
        string $type,
        AdminReferenceDataRegistry $registry,
        AdminReferenceDataService $service,
    ): RedirectResponse {
        $config = $registry->get($type);

        /** @var User $user */
        $user = $request->user();

        $record = $service->create($config, $request->payload(), $user);

        return redirect()->route('admin-console.reference-data.show', [
            'type' => $config['type'],
            'record' => $record->getKey(),
        ]);
    }

    public function show(
        Request $request,
        string $type,
        string $record,
        AdminReferenceDataRegistry $registry,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $config = $registry->get($type);
        $record = $this->record($config, $record);

        Gate::authorize('view', $record);

        $record->load(['sourceRecords.verifier:id,name']);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/ReferenceData/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'record' => (new ReferenceDataResource($record, $config))->resolve($request),
            'definition' => [
                'type' => $config['type'],
                'label' => $config['label'],
                'singular' => $config['singular'],
                'description' => $config['description'],
                'allTypes' => $registry->navigationOptions(),
            ],
            'filamentFallbackUrl' => $this->filamentFallbackUrl($type, $record, $user),
        ]);
    }

    public function edit(
        Request $request,
        string $type,
        string $record,
        AdminReferenceDataRegistry $registry,
        AdminConsoleNavigationService $navigation,
    ): Response {
        $config = $registry->get($type);
        $record = $this->record($config, $record);

        Gate::authorize('update', $record);

        $record->load(['sourceRecords']);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/ReferenceData/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new ReferenceDataFormResource($config, $record, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveReferenceDataRequest $request,
        string $type,
        string $record,
        AdminReferenceDataRegistry $registry,
        AdminReferenceDataService $service,
    ): RedirectResponse {
        $config = $registry->get($type);
        $record = $this->record($config, $record);

        /** @var User $user */
        $user = $request->user();

        $service->update($record, $config, $request->payload(), $user);

        return redirect()->route('admin-console.reference-data.show', [
            'type' => $config['type'],
            'record' => $record->getKey(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function applySearch(Builder $query, string $search, array $config): void
    {
        $columns = array_values(array_intersect($config['fields'], [
            'name',
            'slug',
            'label',
            'code',
            'value',
            'description',
            'notes',
            'office_type',
        ]));

        $query->where(function (Builder $builder) use ($columns, $search): void {
            foreach ($columns as $column) {
                $builder->orWhere($column, 'like', '%'.$search.'%');
            }
        });
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function applySort(Builder $query, string $sort, array $config): void
    {
        match ($sort) {
            'title' => $query->orderBy((string) $config['title']),
            'newest' => $query->latest('created_at'),
            default => $query->orderBy('sort_order')->orderBy((string) $config['title']),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, int>
     */
    private function summary(array $config, AdminReferenceDataService $service): array
    {
        /** @var Builder<Model> $base */
        $base = $config['model']::query();

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'inactive' => (clone $base)->where('is_active', false)->count(),
            'used' => (clone $base)->get()->sum(fn (Model $record): int => $service->usageCount($record, $config)),
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function record(array $config, string $record): Model
    {
        return $config['model']::query()->findOrFail($record);
    }

    private function filamentFallbackUrl(string $type, Model $record, User $user): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $user->canAccessFilamentFallback() || ! $user->can('update', $record)) {
            return null;
        }

        $resource = match ($type) {
            'program-categories' => ProgramCategoryResource::class,
            'project-categories' => ProjectCategoryResource::class,
            'project-statuses' => ProjectStatusResource::class,
            'project-partners' => ProjectPartnerResource::class,
            'news-categories' => NewsCategoryResource::class,
            'news-statuses' => NewsStatusResource::class,
            'legislative-roles' => LegislativeRoleResource::class,
            'legislative-statuses' => LegislativeStatusResource::class,
            'legislative-topics' => LegislativeTopicResource::class,
            'inquiry-categories' => InquiryCategoryResource::class,
            default => null,
        };

        return $resource !== null ? $resource::getUrl('index') : null;
    }
}
