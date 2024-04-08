<?php

use App\Http\Controllers\Api\Events\TicketTypesController;
use App\Http\Controllers\Api\Events\WaiversController;
use App\Http\Controllers\Api\EventsController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Events Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['token.refresh'])->as('api.')->group(function () {
    Orion::resource('events', EventsController::class)->withSoftDeletes();

    Orion::hasManyResource('events', 'ticket-types', TicketTypesController::class)->withSoftDeletes()->except(['associate', 'dissociate']);
    Orion::hasManyResource('events', 'waivers', WaiversController::class)->except(['associate', 'dissociate', 'batchStore', 'batchUpdate', 'batchDestroy', 'batchRestore']);

    Route::post('/events/{event}/waivers/{waiver?}/complete', [WaiversController::class, 'completeAction'])->name('events.waivers.complete');
});
