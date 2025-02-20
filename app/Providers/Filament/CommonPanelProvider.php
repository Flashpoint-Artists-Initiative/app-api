<?php
declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\AvatarProviders\DiceBearProvider;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\View\PanelsRenderHook;

class CommonPanelProvider extends PanelProvider
{
    public string $id;
    
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id($this->id)
            ->brandLogo(asset('logo-text.svg'))
            ->favicon(asset('logo.svg'))
            ->brandLogoHeight('revert-layer')
            ->defaultAvatarProvider(DiceBearProvider::class)
            ->authGuard('web')
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
            ->discoverResources(...$this->discoverHelper('Resources'))
            ->discoverPages(...$this->discoverHelper('Pages'))
            ->discoverWidgets(...$this->discoverHelper('Widgets'));
    }

    /**
     * @return array<string, string>
     */
    public function discoverHelper(string $resource): array
    {
        $uc = ucfirst($this->id);
        return [
            'in' => app_path("Filament/{$uc}/{$resource}"),
            'for' => "App\\Filament\\{$uc}\\{$resource}",
        ];
    }

    public function register(): void
    {
        parent::register();
        FilamentView::registerRenderHook(PanelsRenderHook::BODY_END, fn(): string => Blade::render("@vite('resources/js/app.js')"));
    }
}
