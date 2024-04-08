<?php

use App\Http\Controllers\Api\Admin\AuditController;
use App\Http\Controllers\Api\Admin\CompletedWaiversController;
use App\Http\Controllers\Api\Admin\OrdersController;
use App\Http\Controllers\LockdownController;
use App\Services\LockdownService;
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
    Orion::resource('audits', AuditController::class)->only(['index', 'show', 'search']);

    //Lockdown Routes
    Route::get('lockdown', [LockdownController::class, 'getLockdownStatus'])->whereIn('type', LockdownService::lockdownTypes())->name('lockdown.status');
    Route::post('lockdown/{type}', [LockdownController::class, 'enableLockdown'])->whereIn('type', LockdownService::lockdownTypes())->name('lockdown.enable');
    Route::delete('lockdown/{type}', [LockdownController::class, 'disableLockdown'])->whereIn('type', LockdownService::lockdownTypes())->name('lockdown.disable');
});
