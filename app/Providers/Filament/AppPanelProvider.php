<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Support\Facades\Auth;

class AppPanelProvider extends CommonPanelProvider
{
    public string $id = 'app';

    public function panel(Panel $panel): Panel
    {
        return parent::panel($panel)
            ->default()
            ->path('')
            ->brandLogo(asset('logo-text.svg'))
            ->favicon(asset('logo.svg'))
            ->brandLogoHeight('revert-layer')
            ->login()
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification()
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Violet,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationItems([
                NavigationItem::make('Admin Site')
                    ->url(fn () => route('filament.admin.pages.dashboard'))
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->visible(fn (): bool => Auth::user()?->can('panelAccess.admin') ?? false)
                    ->sort(999),
            ]);
    }

    public function register(): void
    {
        parent::register();
    }
}
