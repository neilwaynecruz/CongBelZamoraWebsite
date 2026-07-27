<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\AdminReportsMetricsService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class ProjectBarangayStatsWidget extends Widget
{
    protected static string $view = 'filament.widgets.project-barangay-stats-widget';

    protected static ?int $sort = -24;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && app(AdminReportsMetricsService::class)->canViewProjectReports($user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return ['rows' => []];
        }

        return [
            'rows' => app(AdminReportsMetricsService::class)->getProjectStatsByBarangay($user),
        ];
    }
}
