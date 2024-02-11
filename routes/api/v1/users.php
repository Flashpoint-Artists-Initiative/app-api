<?php

use App\Http\Controllers\Api\Users\RolesController;
use App\Http\Controllers\Api\Users\TicketsController;
use App\Http\Controllers\Api\Users\TicketTransfersController;
use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->as('api.')->group(function () {
    Orion::resource('users', UsersController::class)->withoutBatch()->withSoftDeletes();
    Orion::belongsToManyResource('users', 'roles', RolesController::class)->only(['index', 'attach', 'detach', 'sync']);

    Orion::hasManyResource('users', 'ticket-transfers', TicketTransfersController::class)->only(['index', 'search', 'show', 'destroy']);
    // creating a transfer take custom input, so we pull it out of Orion
    Route::post('/users/{user}/ticket-transfers', [TicketTransfersController::class, 'transferAction'])->name('users.ticket-transfers.store');

    Route::get('/users/{user}/tickets', [TicketsController::class, 'indexAction'])->name('users.tickets.index');
});
