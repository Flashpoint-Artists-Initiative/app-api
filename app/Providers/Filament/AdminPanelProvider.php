<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectIfNotFilamentAdmin;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Request;

class AdminPanelProvider extends CommonPanelProvider
{
    public string $id = 'admin';

    public function panel(Panel $panel): Panel
    {
        Log::info('asset: ' . asset('images/admin-logo-text.svg'));
        Log::info('host: ' . request()->host());
        Log::info('isFromTrusted: ' . (request()->isFromTrustedProxy() ? 'true' : 'false'));
        Log::info('headers: ' . print_r(request()->header(), true));
        return parent::panel($panel)
            ->path('admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandLogo(fn() => asset('images/admin-logo-text.svg'))
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
                    ->url(fn () => route('filament.app.pages.dashboard'))
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->sort(999),
            ]);
    }

    public function register(): void
    {
        parent::register();
        FilamentView::registerRenderHook(PanelsRenderHook::TOPBAR_START, fn (): string => Blade::render('@livewire(\'event-selector\')'));
    }
}
