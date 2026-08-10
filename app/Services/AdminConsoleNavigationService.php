<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\User;

class AdminConsoleNavigationService
{
    /**
     * @return array<string, mixed>
     */
    public function contextFor(User $user): array
    {
        return [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->admin_role?->value,
                'roleLabel' => $user->admin_role?->label() ?? 'Staff',
                'initials' => str($user->name)
                    ->explode(' ')
                    ->filter()
                    ->take(2)
                    ->map(fn (string $part): string => str($part)->substr(0, 1)->upper()->toString())
                    ->implode(''),
            ],
            'navigation' => $this->for($user),
            'searchUrl' => route('admin-console.search'),
            'dashboardUrl' => route('admin-console.dashboard'),
            'logoutUrl' => route('logout'),
            'filamentFallbackEnabled' => (bool) config('belzamora.admin_console.filament_fallback_enabled', true),
            'environment' => app()->environment(),
        ];
    }

    /**
     * @return list<array{label: string, items: list<array{label: string, description: string, href: string, icon: string, badge: string|null, external: bool}>}>
     */
    public function for(User $user): array
    {
        $groups = [
            [
                'label' => 'Command',
                'items' => [
                    $this->item(
                        'Command Center',
                        'District Office operational overview',
                        route('admin-console.dashboard'),
                        'gauge',
                    ),
                ],
            ],
        ];

        $operations = [];

        if ($user->can('viewAny', Inquiry::class)) {
            $operations[] = $this->item(
                'Inquiry Cases',
                'Read-only case queue and secure dossiers',
                route('admin-console.inquiries.index'),
                'inbox',
            );
        }

        if ($user->can('viewAny', ContentApproval::class) && $this->canViewContentQueue($user)) {
            $operations[] = $this->item(
                'Content Review',
                'Approval queue and governance status',
                route('admin-console.content-approvals.index'),
                'approval',
            );
        }

        if ($operations !== []) {
            $groups[] = [
                'label' => 'Operations',
                'items' => $operations,
            ];
        }

        $tools = [];

        if ($user->hasAdminRole(AdminRole::SuperAdministrator)) {
            $tools[] = $this->item(
                'Design System',
                'Admin component and accessibility reference',
                route('admin-console.design-system'),
                'palette',
            );
        }

        if ((bool) config('belzamora.admin_console.filament_fallback_enabled', true)) {
            $tools[] = $this->item(
                'Legacy Admin Fallback',
                'Temporary access for workflows not yet migrated',
                url('/admin'),
                'legacy',
                'Fallback',
                true,
            );
        }

        if ($tools !== []) {
            $groups[] = [
                'label' => 'Transition Tools',
                'items' => $tools,
            ];
        }

        return $groups;
    }

    /**
     * @return array{label: string, description: string, href: string, icon: string, badge: string|null, external: bool}
     */
    private function item(
        string $label,
        string $description,
        string $href,
        string $icon,
        ?string $badge = null,
        bool $external = false,
    ): array {
        return compact('label', 'description', 'href', 'icon', 'badge', 'external');
    }

    private function canViewContentQueue(User $user): bool
    {
        return $user->canReviewPublicContent()
            || $user->canManagePrograms()
            || $user->canManageLegislation()
            || $user->canManageProjects()
            || $user->canManageNewsMedia()
            || $user->canAuditRecords();
    }
}
