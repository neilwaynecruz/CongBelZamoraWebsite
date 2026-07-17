<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\PublicPageResource;
use App\Models\User;
use App\Services\AdminDashboardMetricsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class Phase7ContentHealthOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -40; // Place it right below the header widget

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User && (
            app(AdminDashboardMetricsService::class)->canViewContentHealth($user)
            || $user->canManageInquiries()
            || $user->canAuditRecords()
        );
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return [];
        }

        $metricsService = app(AdminDashboardMetricsService::class);
        $stats = [];

        if ($metricsService->canViewContentHealth($user)) {
            $counts = $metricsService->getCachedCounts();

            $openPrograms = $counts['open_programs'] ?? 0;
            $pendingApprovals = $counts['pending_approvals'] ?? 0;
            $staleContent = $counts['stale_content'] ?? 0;
            $sourcesDue = $counts['sources_due'] ?? 0;

            $stats = [
                Stat::make('Open programs', $openPrograms)
                    ->description('Active community programs')
                    ->descriptionIcon('heroicon-m-academic-cap')
                    ->color('success')
                    ->url(ProgramResource::getUrl('index')),

                Stat::make('Pending approvals', $pendingApprovals)
                    ->description($pendingApprovals > 0 ? 'Urgent reviews required' : 'All content approved')
                    ->descriptionIcon('heroicon-m-clipboard-document-check')
                    ->color($pendingApprovals > 0 ? 'danger' : 'success')
                    ->url(PublicPageResource::getUrl('index')),

                Stat::make('Stale content', $staleContent)
                    ->description($staleContent > 0 ? 'Review due date passed' : 'All content fresh')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color($staleContent > 0 ? 'warning' : 'success')
                    ->url(PublicPageResource::getUrl('index')),

                Stat::make('Sources due', $sourcesDue)
                    ->description($sourcesDue > 0 ? 'Source verification needed' : 'All sources verified')
                    ->descriptionIcon('heroicon-m-document-magnifying-glass')
                    ->color($sourcesDue > 0 ? 'warning' : 'success')
                    ->url(PublicPageResource::getUrl('index')),
            ];
        }

        if ($user->canManageInquiries() || $user->canAuditRecords()) {
            $unansweredInquiries = $metricsService->getUnansweredInquiriesCount($user);
            $stats[] = Stat::make('Unanswered inquiries', $unansweredInquiries)
                ->description($unansweredInquiries > 0 ? 'Awaiting response' : 'No pending inquiries')
                ->descriptionIcon('heroicon-m-envelope')
                ->color($unansweredInquiries > 0 ? 'warning' : 'success')
                ->url(InquiryResource::getUrl('index'));
        }

        return $stats;
    }
}
