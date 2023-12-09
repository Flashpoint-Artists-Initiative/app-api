<?php

namespace App\Providers;

use App\Http\Controllers\Fortify\AuthenticationController;
use App\Http\Controllers\Fortify\ConfirmablePasswordController;
use App\Http\Controllers\Fortify\NewPasswordController;
use App\Http\Controllers\Fortify\RegisteredUserController;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\NewPasswordController as FortifyNewPasswordController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController as FortifyRegisteredUserController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController as FortifyConfirmablePasswordController;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        AuthenticatedSessionController::class => AuthenticationController::class,
        FortifyNewPasswordController::class => NewPasswordController::class,
        FortifyRegisteredUserController::class => RegisteredUserController::class,
        FortifyConfirmablePasswordController::class => ConfirmablePasswordController::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
