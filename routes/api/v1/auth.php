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
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/user', 'userAction')->middleware(['verified'])->name('auth.user');
        Route::post('/logout', 'logoutAction')->name('logout');
    });
});
