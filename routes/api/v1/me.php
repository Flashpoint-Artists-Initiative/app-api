<?php

use App\Http\Controllers\Api\Me\TicketTransfersController;
use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| (User) Me Routes
|--------------------------------------------------------------------------
*/
Route::controller(MeController::class)->middleware(['auth'])->prefix('me')->as('api.me.')->group(function () {
    Route::get('/', 'indexAction')->name('index');
    Route::match(['PUT', 'PATCH'], '/', 'update')->name('update');
    Route::get('/tickets', 'ticketsAction')->name('tickets');
    Route::get('/orders', 'ordersAction')->name('orders');
    Route::get('/waivers', 'waiversAction')->name('waivers');

    Orion::resource('ticket-transfers', TicketTransfersController::class)->only(['index', 'search', 'show', 'destroy']);
    // creating a transfer take custom input, so we pull it out of Orion
    Route::post('/ticket-transfers', [TicketTransfersController::class, 'transferAction'])->name('users.ticket-transfers.store');
});
