<?php

use App\Http\Controllers\Api\Admin\CompletedWaiversController;
use App\Http\Controllers\Api\Admin\OrdersController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->prefix('admin')->as('api.admin.')->group(function () {
    Orion::resource('completed-waivers', CompletedWaiversController::class)->except(['update', 'batchUpdate']);
    Orion::resource('orders', OrdersController::class)->only(['index', 'show', 'search']);
});
