<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ContentApprovalResource;
use App\Models\User;
use App\Services\AdminReportsMetricsService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PublishLatencyOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -28;

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && app(AdminReportsMetricsService::class)->canViewPublishReports($user);
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [];
        }

        $stats = app(AdminReportsMetricsService::class)->getPublishLatencyStats($user);
        $sample = $stats['sample_size'];
        $avg = $stats['average_hours'];
        $median = $stats['median_hours'];

        return [
            Stat::make('Publish samples', $sample)
                ->description('Published approvals with submit + publish timestamps')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($sample > 0 ? 'success' : 'gray')
                ->url(ContentApprovalResource::getUrl('index')),
            Stat::make('Avg publish latency', $avg === null ? 'n/a' : $avg.' h')
                ->description('Submitted → published')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
            Stat::make('Median publish latency', $median === null ? 'n/a' : $median.' h')
                ->description('Submitted → published')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
}
