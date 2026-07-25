<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\DashboardHeaderWidget;
use App\Filament\Widgets\NeedsAttentionWidget;
use App\Filament\Widgets\Phase7ContentHealthOverview;
use App\Filament\Widgets\RecentAuditActivity;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Bel Zamora District Office')
            ->brandLogo(asset('assets/images/brand/House_of_Representative_Logo_Seal.svg'))
            ->brandLogoHeight('3.25rem')
            ->favicon(asset('assets/images/brand/House_of_Representative_Logo_Seal.svg'))
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->colors([
                'primary' => Color::hex('#0b1f3a'), // Deep Navy
                'danger' => Color::hex('#d62828'),  // Civic Red
                'warning' => Color::hex('#c9a227'), // Warm Gold / Alert Amber
                'success' => Color::hex('#16733c'), // Success Green
                'info' => Color::hex('#1746a2'),    // Royal Blue
                'gray' => Color::Zinc,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Public Content'),
                NavigationGroup::make()
                    ->label('Programs & Services'),
                NavigationGroup::make()
                    ->label('Legislative Records'),
                NavigationGroup::make()
                    ->label('Projects'),
                NavigationGroup::make()
                    ->label('News & Media'),
                NavigationGroup::make()
                    ->label('Inquiries'),
                NavigationGroup::make()
                    ->label('Governance & Audit'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                DashboardHeaderWidget::class,
                Widgets\AccountWidget::class,
                Phase7ContentHealthOverview::class,
                NeedsAttentionWidget::class,
                RecentAuditActivity::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
