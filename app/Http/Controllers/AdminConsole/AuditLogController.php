<?php

namespace App\Http\Controllers\AdminConsole;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminConsole\Audit\AuditLogIndexRequest;
use App\Http\Resources\AdminConsole\AuditLogResource;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request, AdminConsoleNavigationService $navigation): Response
    {
        $filters = $request->filters();

        $query = AuditLog::query()
            ->with('actor:id,name,email')
            ->with('auditable');

        if ($filters['search'] !== '') {
            $this->applySearch($query, $filters['search']);
        }

        if ($filters['event'] !== '') {
            $query->where('event', $filters['event']);
        }

        if ($filters['type'] !== '') {
            $query->where('auditable_type', $filters['type']);
        }

        if ($filters['actor'] === 'system') {
            $query->whereNull('actor_user_id');
        } elseif ($filters['actor'] !== '') {
            $query->where('actor_user_id', (int) $filters['actor']);
        }

        if ($filters['date_from'] !== '') {
            $query->whereDate('occurred_at', '>=', $filters['date_from']);
        }

        if ($filters['date_until'] !== '') {
            $query->whereDate('occurred_at', '<=', $filters['date_until']);
        }

        $this->applySort($query, $filters['sort']);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Audit/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'auditLogs' => AuditLogResource::collection($query->paginate(20)->withQueryString()),
            'filters' => $filters,
            'eventOptions' => $this->eventOptions(),
            'typeOptions' => $this->typeOptions(),
            'actorOptions' => $this->actorOptions(),
            'summary' => $this->summary(),
            'exports' => [
                'enabled' => false,
                'reason' => 'Custom audit exports remain disabled until privacy-reviewed export policies are approved for this console.',
            ],
        ]);
    }

    public function show(Request $request, AuditLog $auditLog, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('view', $auditLog);

        $auditLog->load(['actor:id,name,email', 'auditable']);

        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Audit/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'auditLog' => (new AuditLogResource($auditLog))->resolve($request),
        ]);
    }

    private function applySearch(Builder $query, string $search): void
    {
        $query->where(function (Builder $builder) use ($search): void {
            $builder
                ->where('event', 'like', '%'.$search.'%')
                ->orWhere('auditable_type', 'like', '%'.$search.'%')
                ->orWhere('auditable_id', is_numeric($search) ? (int) $search : -1)
                ->orWhereHas('actor', function (Builder $actor) use ($search): void {
                    $actor
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
        });
    }

    private function applySort(Builder $query, string $sort): void
    {
        match ($sort) {
            'event' => $query->orderBy('event')->latest('occurred_at'),
            'type' => $query->orderBy('auditable_type')->latest('occurred_at'),
            'actor' => $query
                ->leftJoin('users', 'audit_logs.actor_user_id', '=', 'users.id')
                ->select('audit_logs.*')
                ->orderBy('users.name')
                ->latest('audit_logs.occurred_at'),
            'oldest' => $query->oldest('occurred_at'),
            default => $query->latest('occurred_at'),
        };
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function eventOptions(): array
    {
        return AuditLog::query()
            ->select('event')
            ->distinct()
            ->orderBy('event')
            ->pluck('event')
            ->map(fn (string $event): array => [
                'value' => $event,
                'label' => Str::of($event)->replace(['.', '_'], ' ')->headline()->toString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return AuditLog::query()
            ->select('auditable_type')
            ->whereNotNull('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type')
            ->map(fn (string $type): array => [
                'value' => $type,
                'label' => Str::of(class_basename($type))->headline()->toString(),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{value: int|string, label: string}>
     */
    private function actorOptions(): array
    {
        return User::query()
            ->whereIn('id', AuditLog::query()->select('actor_user_id')->whereNotNull('actor_user_id'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user): array => [
                'value' => $user->id,
                'label' => $user->name,
            ])
            ->prepend([
                'value' => 'system',
                'label' => 'System',
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function summary(): array
    {
        $base = AuditLog::query();

        return [
            'total' => (clone $base)->count(),
            'today' => (clone $base)->whereDate('occurred_at', now('Asia/Manila')->toDateString())->count(),
            'system' => (clone $base)->whereNull('actor_user_id')->count(),
            'withNetworkHash' => (clone $base)
                ->where(function (Builder $query): void {
                    $query
                        ->whereNotNull('ip_address_hash')
                        ->orWhereNotNull('user_agent_hash');
                })
                ->count(),
        ];
    }
}
