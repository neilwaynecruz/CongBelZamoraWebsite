<?php

namespace App\Services;

use App\Enums\AdminRole;
use App\Models\AuditLog;
use App\Models\ContentApproval;
use App\Models\Inquiry;
use App\Models\LegislativeRecord;
use App\Models\NewsRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\PublicPage;
use App\Models\User;

class AdminConsoleNavigationService
{
    public function __construct(private readonly AdminReportsMetricsService $reportsMetrics) {}

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

        if ($user->can('viewAny', Program::class)) {
            $operations[] = $this->item(
                'Programs',
                'Program records, sources, and approval readiness',
                route('admin-console.programs.index'),
                'programs',
            );
        }

        if ($user->can('viewAny', Project::class)) {
            $operations[] = $this->item(
                'Projects',
                'Project records, evidence, and source readiness',
                route('admin-console.projects.index'),
                'projects',
            );
        }

        if ($user->can('viewAny', LegislativeRecord::class)) {
            $operations[] = $this->item(
                'Legislation',
                'Bills, roles, status, topics, and official sources',
                route('admin-console.legislation.index'),
                'legislation',
            );
        }

        if ($user->can('viewAny', NewsRecord::class)) {
            $operations[] = $this->item(
                'News & Media',
                'Official updates, stale review, and media rights',
                route('admin-console.news.index'),
                'news',
            );
        }

        if ($user->can('viewAny', PublicPage::class)) {
            $operations[] = $this->item(
                'Public Pages',
                'Static pages, SEO metadata, sources, and review readiness',
                route('admin-console.public-pages.index'),
                'publicPages',
            );
        }

        if ($operations !== []) {
            $groups[] = [
                'label' => 'Operations',
                'items' => $operations,
            ];
        }

        $governance = [];

        if ($this->canViewReports($user)) {
            $governance[] = $this->item(
                'Reports',
                'Aggregate inquiry, publishing, freshness, and project metrics',
                route('admin-console.reports'),
                'reports',
            );
        }

        if ($user->can('viewAny', AuditLog::class)) {
            $governance[] = $this->item(
                'Audit Trail',
                'Redacted governed activity ledger',
                route('admin-console.audit.index'),
                'audit',
            );
        }

        if ($governance !== []) {
            $groups[] = [
                'label' => 'Governance',
                'items' => $governance,
            ];
        }

        $tools = [];

        if ($user->hasAdminRole(AdminRole::SuperAdministrator)) {
            $tools[] = $this->item(
                'Staff Users',
                'Roles, access status, and 2FA readiness',
                route('admin-console.users.index'),
                'users',
            );

            $tools[] = $this->item(
                'Settings',
                'Read-only launch and security configuration',
                route('admin-console.settings'),
                'settings',
            );

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

    private function canViewReports(User $user): bool
    {
        return $this->reportsMetrics->canViewInquiryReports($user)
            || $this->reportsMetrics->canViewPublishReports($user)
            || $this->reportsMetrics->canViewStaleReports($user)
            || $this->reportsMetrics->canViewProjectReports($user);
    }
}
