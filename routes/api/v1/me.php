<?php

use App\Http\Controllers\Api\Me\TicketsController;
use App\Http\Controllers\Api\Me\TicketTransfersController;
use App\Http\Controllers\Api\MeController;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| (User) Me Routes
|--------------------------------------------------------------------------
*/
Route::controller(MeController::class)->middleware(['auth', 'token.refresh'])->prefix('me')->as('api.me.')->group(function () {
    Route::get('/', 'indexAction')->name('index');
    Route::match(['PUT', 'PATCH'], '/', 'update')->name('update')->middleware(['lockdown']);
    Route::get('/orders', 'ordersAction')->name('orders');
    Route::get('/waivers', 'waiversAction')->name('waivers');

    Route::get('/tickets', 'ticketsAction')->name('tickets');
    Route::get('/tickets/qr/{event_id?}', [TicketsController::class, 'qrAction'])->name('tickets.qr')->withoutMiddleware(ForceJsonResponse::class);

    Route::get('/ticket-transfers/received', [TicketTransfersController::class, 'received'])->name('ticket-transfers.index.received');
    Orion::resource('ticket-transfers', TicketTransfersController::class)->only(['index', 'search', 'show', 'destroy']);

    Route::middleware(['lockdown:ticket'])->group(function () {
        // creating a transfer take custom input, so we pull it out of Orion
        Route::post('/ticket-transfers', [TicketTransfersController::class, 'transferAction'])->name('ticket-transfers.store');
        Route::post('/ticket-transfers/{ticket_transfer}/complete', [TicketTransfersController::class, 'completeAction'])->name('ticket-transfers.complete');
    });
});
