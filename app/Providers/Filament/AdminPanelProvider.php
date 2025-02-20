<?php
declare(strict_types=1);

namespace App\Providers\Filament;

use App\Http\Middleware\RedirectIfNotFilamentAdmin;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
            ])
            ->navigationItems([
                NavigationItem::make('Return to Main Site')
                    ->url(fn() => route('filament.app.pages.dashboard'))
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->sort(999)
            ]);
    }
}
