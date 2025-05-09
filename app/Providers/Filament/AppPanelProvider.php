<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;


class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->login()
            ->profile()
            ->registration()
            ->passwordReset()
            ->colors([
                'primary' => Color::Violet,
                'secondary' => Color::Cyan,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Blue
            ])
            ->userMenuItems([
                'Profile' => MenuItem::make()->url(fn(): string => \Filament\Pages\Auth\EditProfile::getUrl())
            ])
            ->navigationItems([
                // NavigationItem::make('Asset Report')
                //     ->url(fn(): string => AssetReport::getUrl())
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->group('Reports')
                //     ->sort(1)
                //     ->visible(fn() => auth()->check() && auth()->user()->hasRole(['super_admin', 'admin', 'technician'])),
                // NavigationItem::make('Inventory Report')
                //     ->url(fn(): string => InventoryReport::getUrl())
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->group('Reports')
                //     ->sort(1)
                //     ->visible(fn() => auth()->check() && auth()->user()->hasRole(['super_admin', 'admin', 'technician'])),
                // NavigationItem::make('Maintenance Report')
                //     ->url(fn(): string => MaintenanceReport::getUrl())
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->group('Reports')
                //     ->sort(1)
                //     ->visible(fn() => auth()->check() && auth()->user()->hasRole(['super_admin', 'admin', 'technician'])),
                // NavigationItem::make('Users Report')
                //     ->url(fn(): string => UsersReport::getUrl())
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->group('Reports')
                //     ->sort(1)
                //     ->visible(fn() => auth()->check() && auth()->user()->hasRole(['super_admin', 'admin', 'technician'])),
                // NavigationItem::make('Activity Log')
                //     ->url(fn(): string => ActivityResource::getUrl())
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->group('System Settings')
                //     ->sort(1)
                //     ->visible(fn() => auth()->check() && auth()->user()->hasRole(['super_admin', 'admin', 'technician'])),
                // NavigationItem::make('Category Report')
                //     ->url(fn(): string => CategoryReport::getUrl())
                //     ->icon('heroicon-o-presentation-chart-line')
                //     ->group('Reports')
                //     ->sort(1)
                //     ->visible(fn() => auth()->check() && auth()->user()->hasRole(['super_admin', 'admin', 'technician'])),
            ])
            ->navigationGroups([
                'Assets',
                'Schedule',
                'Tickets',
                'School',
                'Report Builder',
                'System Settings',
            ])
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([])
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
            ])
            ->plugins([
                \EightyNine\Reports\ReportsPlugin::make(),
                \Awcodes\LightSwitch\LightSwitchPlugin::make(),
                \Afsakar\FilamentOtpLogin\FilamentOtpLoginPlugin::make(),
                \Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin::make(),
                \Awcodes\FilamentQuickCreate\QuickCreatePlugin::make()
                    ->includes([
                        \App\Filament\App\Resources\AssetResource::class,
                        \App\Filament\App\Resources\BuildingResource::class,
                        \App\Filament\App\Resources\ClassroomResource::class,
                        \App\Filament\App\Resources\SectionResource::class,
                        \App\Filament\App\Resources\SubjectResource::class,
                        \App\Filament\App\Resources\UserResource::class,
                        \App\Filament\App\Resources\RoleResource::class,
                    ]),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->gridColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 3
                    ])
                    ->sectionColumnSpan(1)
                    ->checkboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 2,
                    ])
                    ->resourceCheckboxListColumns([
                        'default' => 1,
                        'sm' => 2,
                    ]),
                \Saade\FilamentFullCalendar\FilamentFullCalendarPlugin::make()
                    ->schedulerLicenseKey('')
                    ->selectable(true)
                    ->editable()
                    ->timezone(config('app.timezone'))
                    ->locale(config('app.locale'))
                    ->plugins(['dayGrid', 'timeGrid'])
                    ->config([])
            ])
            ->breadcrumbs(false)
            ->resources([
                config('filament-logger.activity_resource')
            ]);
    }
}
