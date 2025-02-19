<?php
declare(strict_types=1);

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Support\Facades\Vite;

class CommonPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->brandLogo(asset('logo-text.svg'))
            ->favicon(asset('logo.svg'))
            ->brandLogoHeight('revert-layer')
            ->renderHook('panels::head.start',
                fn(): string => Vite::useHotFile(public_path('hot'))
                    ->withEntryPoints(['resources/js/app.js'])->toHtml());
    }
}
