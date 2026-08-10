<?php

namespace App\Http\Controllers\AdminConsole;

use App\Enums\AdminRole;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminConsole\InquiryDetailResource;
use App\Http\Resources\AdminConsole\InquirySummaryResource;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\AdminConsoleNavigationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function index(Request $request, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('viewAny', Inquiry::class);

        /** @var User $user */
        $user = $request->user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'queue' => ['nullable', 'in:overdue,assigned,unassigned'],
            'sort' => ['nullable', 'in:newest,oldest,due'],
        ]);

        $query = Inquiry::query()
            ->with('assignedOfficer:id,name')
            ->select([
                'id',
                'reference_code',
                'inquiry_category',
                'priority',
                'status',
                'office_unit',
                'assigned_officer_id',
                'assigned_at',
                'first_response_due_at',
                'first_responded_at',
                'responded_at',
                'created_at',
            ]);

        if ($user->hasAdminRole(AdminRole::InquiryOfficer)) {
            $query->where(fn (Builder $builder) => $builder
                ->whereNull('assigned_officer_id')
                ->orWhere('assigned_officer_id', $user->id));
        }

        if (filled($filters['search'] ?? null)) {
            $query->where('reference_code', 'like', '%'.trim((string) $filters['search']).'%');
        }

        if (($status = InquiryStatus::tryFrom((string) ($filters['status'] ?? ''))) !== null) {
            $query->where('status', $status->value);
        }

        if (($priority = InquiryPriority::tryFrom((string) ($filters['priority'] ?? ''))) !== null) {
            $query->where('priority', $priority->value);
        }

        match ($filters['queue'] ?? null) {
            'overdue' => $query->overdueFirstResponse(),
            'assigned' => $query->where('assigned_officer_id', $user->id)->openCase(),
            'unassigned' => $query->whereNull('assigned_officer_id')->openCase(),
            default => null,
        };

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest('created_at'),
            'due' => $query->orderByRaw('first_response_due_at is null, first_response_due_at asc'),
            default => $query->latest('created_at'),
        };

        $inquiries = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Inquiries/Index', [
            'adminConsole' => $navigation->contextFor($user),
            'inquiries' => InquirySummaryResource::collection($inquiries),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'priority' => $filters['priority'] ?? '',
                'queue' => $filters['queue'] ?? '',
                'sort' => $filters['sort'] ?? 'newest',
            ],
            'statusOptions' => InquiryStatus::options(),
            'priorityOptions' => InquiryPriority::options(),
        ]);
    }

    public function show(Request $request, Inquiry $inquiry, AdminConsoleNavigationService $navigation): Response
    {
        Gate::authorize('view', $inquiry);

        /** @var User $user */
        $user = $request->user();
        $inquiry->load([
            'assignedOfficer:id,name',
            'chronologicalActivities.actor:id,name',
        ]);

        return Inertia::render('Admin/Inquiries/Show', [
            'adminConsole' => $navigation->contextFor($user),
            'inquiry' => (new InquiryDetailResource($inquiry))->resolve($request),
            'workflow' => $this->workflowFor($user, $inquiry),
        ]);
    }

    /** @return array<string, mixed> */
    private function workflowFor(User $user, Inquiry $inquiry): array
    {
        $canAssign = $user->can('assign', $inquiry);
        $templates = config('belzamora.inquiries.canned_replies', []);
        $closureReasons = config('belzamora.inquiries.case_management.closure_reasons', []);

        return [
            'writable' => $user->can('update', $inquiry),
            'ownershipRequired' => $inquiry->assigned_officer_id === null,
            'currentUserId' => $user->id,
            'assignees' => $canAssign
                ? User::query()
                    ->where('is_active', true)
                    ->whereIn('admin_role', [
                        AdminRole::SuperAdministrator->value,
                        AdminRole::InquiryOfficer->value,
                    ])
                    ->orderBy('name')
                    ->get(['id', 'name', 'admin_role'])
                    ->map(fn (User $officer): array => [
                        'id' => $officer->id,
                        'name' => $officer->name,
                        'role' => $officer->admin_role?->label() ?? 'Authorized staff',
                    ])
                    ->values()
                    ->all()
                : [],
            'templates' => is_array($templates)
                ? collect($templates)->map(fn (mixed $text, mixed $key): array => [
                    'key' => (string) $key,
                    'label' => str((string) $key)->replace('_', ' ')->headline()->toString(),
                    'text' => (string) $text,
                ])->values()->all()
                : [],
            'closureReasons' => is_array($closureReasons)
                ? collect($closureReasons)->map(fn (mixed $label, mixed $value): array => [
                    'value' => (string) $value,
                    'label' => (string) $label,
                ])->values()->all()
                : [],
            'actions' => [
                'assign' => $this->action($canAssign, route('admin-console.inquiries.assign', $inquiry)),
                'markUnderReview' => $this->action($user->can('markUnderReview', $inquiry), route('admin-console.inquiries.mark-under-review', $inquiry)),
                'addInternalNote' => $this->action($user->can('addInternalNote', $inquiry), route('admin-console.inquiries.notes.store', $inquiry)),
                'recordResponse' => $this->action($user->can('respond', $inquiry), route('admin-console.inquiries.responses.store', $inquiry)),
                'resolve' => $this->action($user->can('resolve', $inquiry), route('admin-console.inquiries.resolve', $inquiry)),
                'close' => $this->action($user->can('close', $inquiry), route('admin-console.inquiries.close', $inquiry)),
                'reopen' => $this->action($user->can('reopen', $inquiry), route('admin-console.inquiries.reopen', $inquiry)),
            ],
        ];
    }

    /** @return array{allowed: bool, url: string} */
    private function action(bool $allowed, string $url): array
    {
        return compact('allowed', 'url');
    }
}
