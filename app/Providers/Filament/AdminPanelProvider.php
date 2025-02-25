<?php
declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectIfNotFilamentAdmin;
use Blade;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;

class AdminPanelProvider extends CommonPanelProvider
{
    public string $id = 'admin';

    public function panel(Panel $panel): Panel
    {
        return parent::panel($panel)
            ->path('admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(asset('admin-logo-text.svg'))
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->authMiddleware([
                RedirectIfNotFilamentAdmin::class,
                Authenticate::class,
            ])
            ->navigationItems([
                NavigationItem::make('Return to Main Site')
                    ->url(fn() => route('filament.app.pages.dashboard'))
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->sort(999)
            ]);
    }

    public function register(): void
    {
        parent::register();
        FilamentView::registerRenderHook(PanelsRenderHook::TOPBAR_START, fn(): string => Blade::render('@livewire(\'event-selector\')'));
    }

}
