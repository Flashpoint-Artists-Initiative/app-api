<?php

use App\Http\Controllers\Api\Events\PurchasedTicketsController;
use App\Http\Controllers\Api\Events\ReservedTicketsController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->as('api.')->group(function () {
    Orion::hasManyResource('ticket-types', 'reserved-tickets', ReservedTicketsController::class)->middleware(['auth']);
    Orion::hasManyResource('ticket-types', 'purchased-tickets', PurchasedTicketsController::class)->only([
        'index',
        'search',
        'show',
    ]);
});
