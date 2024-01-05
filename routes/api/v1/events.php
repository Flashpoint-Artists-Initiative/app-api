<?php

use App\Http\Controllers\Api\Events\PurchasedTicketsController;
use App\Http\Controllers\Api\Events\ReservedTicketsController;
use App\Http\Controllers\Api\Events\TicketTypesController;
use App\Http\Controllers\Api\EventsController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['token.refresh'])->as('api.')->group(function () {
    Orion::resource('events', EventsController::class)->withSoftDeletes();
    Orion::hasManyResource('events', 'ticket-types', TicketTypesController::class)->withSoftDeletes()->except(['associate', 'dissociate']);

    // TODO: Determine if these routes are needed or not
    // Orion::hasManyThroughResource('events', 'purchased-tickets', PurchasedTicketsController::class);
    // Orion::hasManyThroughResource('events', 'reserved-tickets', ReservedTicketsController::class);
});
