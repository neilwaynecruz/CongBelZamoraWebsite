<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Services\AdminReportsMetricsService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class InquiryVolumeByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Inquiry volume by category (30 days)';

    protected static ?int $sort = -30;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user instanceof User
            && app(AdminReportsMetricsService::class)->canViewInquiryReports($user);
    }

    protected function getData(): array
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $rows = app(AdminReportsMetricsService::class)->getInquiryVolumeByCategory($user);

        return [
            'datasets' => [
                [
                    'label' => 'Inquiries',
                    'data' => collect($rows)->pluck('total')->all(),
                    'backgroundColor' => '#1746a2',
                ],
            ],
            'labels' => collect($rows)->pluck('label')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
