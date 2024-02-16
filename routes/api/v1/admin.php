<?php

use App\Http\Controllers\Api\CompletedWaiversController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->prefix('admin')->as('api.admin.')->group(function () {
    Orion::resource('completed-waivers', CompletedWaiversController::class)->except(['update', 'batchUpdate']);
});
