<?php

use Illuminate\Support\Facades\Route;

// This is a dumb way to make the FilamentInactivityGuard work with 
// the admin panel that doesn't have it's own login page
Route::get('login-redirect', function () {
    return redirect()->route('filament.app.auth.login');
})->name('login');