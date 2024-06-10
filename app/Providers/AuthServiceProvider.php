<?php

declare(strict_types=1);

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Enums\RolesEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // TODO: Update this URL
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $host = config('app.url');

            return "$host/reset-password?token=" . $token;
        });

        // TODO: Update this URL
        VerifyEmail::createUrlUsing(function ($notifiable) {
            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());
            $url = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $id,
                    'hash' => $hash,
                ]
            );

            $queryString = parse_url($url, PHP_URL_QUERY);
            $host = config('app.url');
            $path = preg_replace(['/\{id\}/', '/\{hash\}/'], [$id, $hash], config('mail.verifyEmailPath'));

            return "{$host}{$path}?$queryString";
        });

        // Allow public access to the API docs in non-local environments
        Gate::define('viewApiDocs', fn (?User $user) => true);

        // Allow super user to do anything
        Gate::after(fn (User $user, $ability) => $user->hasRole(RolesEnum::SuperAdmin));
    }
}
