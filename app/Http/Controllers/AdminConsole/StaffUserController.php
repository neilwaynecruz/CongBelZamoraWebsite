<?php

namespace App\Http\Controllers\AdminConsole;

use App\Enums\AdminRole;
use App\Filament\Resources\UserResource;
use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Users\SaveStaffUserRequest;
use App\Http\Resources\AdminConsole\StaffUserFormResource;
use App\Http\Resources\AdminConsole\StaffUserResource;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use App\Services\AdminStaffUserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class StaffUserController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', User::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', new Enum(AdminRole::class)],
            'status' => ['nullable', 'in:active,deactivated'],
            'two_factor' => ['nullable', 'in:enabled,missing,required_missing'],
            'sort' => ['nullable', 'in:name,email,role,status,updated,newest'],
        ]);

        $query = User::query()
            ->whereNotNull('admin_role');

        if (filled($filters['search'] ?? null)) {
            $this->applySearch($query, trim((string) $filters['search']));
        }

        if (filled($filters['role'] ?? null)) {
            $query->where('admin_role', (string) $filters['role']);
        }

        if (($filters['status'] ?? null) === 'active') {
            $query->where('is_active', true);
        }

        if (($filters['status'] ?? null) === 'deactivated') {
            $query->where('is_active', false);
        }

        $this->applyTwoFactorFilter($query, (string) ($filters['two_factor'] ?? ''));
        $this->applySort($query, (string) ($filters['sort'] ?? 'name'));

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Users/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'staffUsers' => StaffUserResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => $filters['role'] ?? '',
                'status' => $filters['status'] ?? '',
                'two_factor' => $filters['two_factor'] ?? '',
                'sort' => $filters['sort'] ?? 'name',
            ],
            'roleOptions' => $this->roleOptions(),
            'summary' => $this->summary(),
            'security' => [
                'twoFactorRequired' => (bool) config('belzamora.staff.require_2fa', false),
            ],
            'can' => [
                'create' => $user->can('create', User::class),
            ],
        ]);
    }

    public function create(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('create', User::class);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Users/Edit', [
            'adminConsole' => $navigation->contextFor($user),
            'form' => (new StaffUserFormResource(null, 'create'))->toArray($request),
        ]);
    }

    public function store(
        SaveStaffUserRequest $request,
        AdminStaffUserService $service,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();

        $staffUser = $service->create($request->payload(), $actor, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Staff user created in the custom command system.',
        ]);

        return redirect()->route('admin-console.users.show', $staffUser);
    }

    public function show(
        Request $request,
        User $user,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('view', $user);

        /** @var User $actor */
        $actor = $request->user();

        return Inertia::render('Admin/Users/Show', [
            'adminConsole' => $navigation->contextFor($actor),
            'staffUser' => (new StaffUserResource($user))->resolve($request),
            'filamentFallbackUrl' => $this->filamentFallbackUrl($user, $actor),
            'resetPasswordUrl' => route('admin-console.users.password-reset', $user),
        ]);
    }

    public function edit(
        Request $request,
        User $user,
        AdminConsoleNavigationService $navigation,
    ): Response {
        Gate::authorize('update', $user);

        /** @var User $actor */
        $actor = $request->user();

        return Inertia::render('Admin/Users/Edit', [
            'adminConsole' => $navigation->contextFor($actor),
            'form' => (new StaffUserFormResource($user, 'edit'))->toArray($request),
        ]);
    }

    public function update(
        SaveStaffUserRequest $request,
        User $user,
        AdminStaffUserService $service,
    ): RedirectResponse {
        /** @var User $actor */
        $actor = $request->user();

        $service->update($user, $request->payload(), $actor, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Staff user updated in the custom command system.',
        ]);

        return redirect()->route('admin-console.users.show', $user);
    }

    public function sendPasswordReset(
        Request $request,
        User $user,
        AdminStaffUserService $service,
    ): RedirectResponse {
        Gate::authorize('update', $user);

        /** @var User $actor */
        $actor = $request->user();

        $service->sendPasswordResetLink($user, $actor, $request);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Password reset link sent through Laravel password reset.',
        ]);

        return back();
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('admin_role', 'like', '%'.$search.'%');
        });
    }

    private function applyTwoFactorFilter(Builder $query, string $filter): void
    {
        match ($filter) {
            'enabled' => $query
                ->whereNotNull('two_factor_secret')
                ->whereNotNull('two_factor_confirmed_at'),
            'missing', 'required_missing' => $query->where(function (Builder $builder): void {
                $builder
                    ->whereNull('two_factor_secret')
                    ->orWhereNull('two_factor_confirmed_at');
            }),
            default => null,
        };
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'email' => $query->orderBy('email'),
            'role' => $query->orderBy('admin_role')->orderBy('name'),
            'status' => $query->orderByDesc('is_active')->orderBy('name'),
            'updated' => $query->latest('updated_at'),
            'newest' => $query->latest('created_at'),
            default => $query->orderBy('name'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function roleOptions(): array
    {
        return collect(AdminRole::cases())
            ->map(fn (AdminRole $role): array => [
                'value' => $role->value,
                'label' => $role->label(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = User::query()->whereNotNull('admin_role');

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('is_active', true)->count(),
            'deactivated' => (clone $base)->where('is_active', false)->count(),
            'missingTwoFactor' => (clone $base)
                ->where(function (Builder $query): void {
                    $query
                        ->whereNull('two_factor_secret')
                        ->orWhereNull('two_factor_confirmed_at');
                })
                ->count(),
        ];
    }

    private function filamentFallbackUrl(User $staffUser, User $actor): ?string
    {
        if (! (bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            return null;
        }

        if (! $actor->canAccessFilamentFallback() || ! $actor->can('update', $staffUser)) {
            return null;
        }

        return UserResource::getUrl('edit', ['record' => $staffUser]);
    }
}
