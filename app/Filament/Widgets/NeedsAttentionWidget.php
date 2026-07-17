<?php

namespace App\Filament\Widgets;

use App\Services\AdminDashboardMetricsService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class NeedsAttentionWidget extends Widget
{
    protected static string $view = 'filament.widgets.needs-attention-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -30; // Below stats overview

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null && app(AdminDashboardMetricsService::class)->canViewNeedsAttention($user);
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Auth::user();
        if (! $user) {
            return ['items' => []];
        }

        $metricsService = app(AdminDashboardMetricsService::class);
        $items = $metricsService->getNeedsAttentionItems($user);

        return [
            'items' => $items,
        ];
    }
}
