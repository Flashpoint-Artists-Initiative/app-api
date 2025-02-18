<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class RedirectIfNotFilamentAdmin extends Middleware
{

    /**
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards)
    {
        $auth = Filament::auth();

        if (!$auth->check()) {
            $this->unauthenticated($request, $guards);

            return; /** @phpstan-ignore-line */
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        /** @var Model $user */
        $user = $auth->user();

        $panel = Filament::getCurrentPanel();

        if ($user instanceof FilamentUser && !is_null($panel)) {
            if (!$user->canAccessPanel($panel)) {
                return redirect(route('filament.app.pages.dashboard')); /** @phpstan-ignore-line */
            }
        }
    }

    protected function redirectTo($request): ?string
    {
          return route('filament.app.auth.login');
    }
}
