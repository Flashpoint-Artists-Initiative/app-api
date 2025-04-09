<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Register;
use Filament\Http\Middleware\Authenticate;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Pages\Auth\Login;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset;
use Filament\Panel;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;

class AppPanelProvider extends CommonPanelProvider
{
    public string $id = 'app';

    public function panel(Panel $panel): Panel
    {
        return parent::panel($panel)
            ->default()
            ->path('')
            ->login()
            ->registration(Register::class)
            ->passwordReset()
            ->emailVerification()
            // ->profile(EditProfile::class, isSimple: false)
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
                    ->visible(fn (): bool => filament()->auth()->user()?->can('panelAccess.admin') ?? false)
                    ->sort(999),
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    // @phpstan-ignore argument.type
                    ->label(fn () => filament()->getUserName(filament()->auth()->user()))
                    ->url(fn () => route('filament.app.profile'))
                    ->icon('heroicon-o-user-circle'),
            ]);
    }

    public function register(): void
    {
        parent::register();
        
        FilamentView::registerRenderHook(PanelsRenderHook::FOOTER, 
            fn (): View => view('panel-footer'), 
            // [Login::class, Register::class, RequestPasswordReset::class] // For now, show on every page
        );

    }
}
