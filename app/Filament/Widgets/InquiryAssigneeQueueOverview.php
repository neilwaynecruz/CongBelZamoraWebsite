<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\InquiryResource;
use App\Models\User;
use App\Services\AdminDashboardMetricsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InquiryAssigneeQueueOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -35;

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && ($user->canManageInquiries() || $user->canAuditRecords());
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        $counts = app(AdminDashboardMetricsService::class)->getInquiryQueueCounts($user);

        return [
            Stat::make('My open cases', $counts['assigned_open'])
                ->description($counts['assigned_open'] > 0 ? 'Assigned inquiries in progress' : 'No assigned open cases')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color($counts['assigned_open'] > 0 ? 'info' : 'success')
                ->url(InquiryResource::getUrl('index', ['tableFilters[my_inquiries][isActive]' => true])),
            Stat::make('My overdue cases', $counts['assigned_overdue'])
                ->description($counts['assigned_overdue'] > 0 ? 'First response target missed' : 'No overdue assigned cases')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($counts['assigned_overdue'] > 0 ? 'danger' : 'success')
                ->url(InquiryResource::getUrl('index', ['tableFilters[my_needs_attention][isActive]' => true])),
            Stat::make('Unassigned open cases', $counts['unassigned_open'])
                ->description($counts['unassigned_open'] > 0 ? 'Needs assignment owner' : 'No unassigned open cases')
                ->descriptionIcon('heroicon-m-inbox')
                ->color($counts['unassigned_open'] > 0 ? 'warning' : 'success')
                ->url(InquiryResource::getUrl('index', ['tableFilters[unassigned][isActive]' => true])),
            Stat::make('Resolved today', $counts['resolved_today'])
                ->description('Cases resolved in Manila time')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(InquiryResource::getUrl('index')),
        ];
    }
}
