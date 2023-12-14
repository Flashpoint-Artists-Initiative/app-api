<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
Route::controller(AuthController::class)->group(function () {
    Route::middleware(['guest:api'])->group(function () {
        Route::post('/login', 'loginAction')->name('login');
        Route::post('/register', 'registerAction')->name('register');
        Route::post('/forgot-password', 'forgotPasswordAction')->name('password.email');
        Route::post('/reset-password', 'resetPasswordAction')->name('password.update');
    });

    Route::post('/logout', 'logoutAction')->middleware(['auth'])->name('logout');

    Route::middleware(['auth', 'jwt.refresh'])->group(function () {
        Route::get('/user', 'userAction')->middleware(['verified'])->name('auth.user');

        Route::prefix('email')->group(function () {
            Route::get('/verify/{id}/{hash}', 'verifyEmailAction')->middleware(['signed'])->name('verification.verify');
            Route::post('resent-verification', 'resendVerificationEmailAction')->middleware(['throttle:6,1'])->name('verification.send');
        });
    });

});
