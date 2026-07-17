<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AuditLogResource;
use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\NewsRecordResource;
use App\Filament\Resources\PublicPageResource;
use App\Models\User;
use App\Services\AuditLogService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class DashboardHeaderWidget extends Widget
{
    protected static string $view = 'filament.widgets.dashboard-header-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -50; // Place it at the very top of the dashboard

    public static function canView(): bool
    {
        return Auth::check();
    }

    public function checkRequiredPublicPages(): void
    {
        $user = Auth::user();

        abort_unless(
            $user instanceof User && ($user->canReviewPublicContent() || $user->canAuditRecords()),
            403,
        );

        $exitCode = Artisan::call('public-pages:check-required');
        $output = trim(Artisan::output());

        app(AuditLogService::class)->record(
            event: 'admin_dashboard_health_check_run',
            actor: $user,
            request: request(),
            metadata: [
                'check' => 'required_public_pages',
                'exit_code' => $exitCode,
                'result' => $exitCode === Command::SUCCESS ? 'passed' : 'failed',
            ],
        );

        Notification::make()
            ->title($exitCode === Command::SUCCESS ? 'Required pages are healthy' : 'Required pages need attention')
            ->body($output !== '' ? $output : 'The required public page check finished.')
            ->status($exitCode === Command::SUCCESS ? 'success' : 'warning')
            ->send();
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        $user = Auth::user();

        return [
            'userName' => $user instanceof User ? $user->name : 'Staff member',
            'currentTime' => now()->timezone('Asia/Manila'),
            'actions' => $user instanceof User ? $this->getQuickActions($user) : [],
            'canRunHealthCheck' => $user instanceof User && ($user->canReviewPublicContent() || $user->canAuditRecords()),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string, icon: string, color: string}>
     */
    private function getQuickActions(User $user): array
    {
        $actions = [];

        if ($user->canReviewPublicContent()) {
            $actions[] = [
                'label' => 'Review Content',
                'url' => PublicPageResource::getUrl('index'),
                'icon' => 'heroicon-s-clipboard-document-check',
                'color' => 'gold',
            ];
        }

        if ($user->canManageNewsMedia()) {
            $actions[] = [
                'label' => 'Create News',
                'url' => NewsRecordResource::getUrl('create'),
                'icon' => 'heroicon-s-document-plus',
                'color' => 'blue',
            ];
        }

        if ($user->canManageInquiries()) {
            $actions[] = [
                'label' => 'Open Inquiries',
                'url' => InquiryResource::getUrl('index'),
                'icon' => 'heroicon-s-envelope-open',
                'color' => 'navy',
            ];
        }

        if ($user->canAuditRecords()) {
            $actions[] = [
                'label' => 'View Audit Log',
                'url' => AuditLogResource::getUrl('index'),
                'icon' => 'heroicon-s-shield-check',
                'color' => 'red',
            ];
        }

        return $actions;
    }
}
