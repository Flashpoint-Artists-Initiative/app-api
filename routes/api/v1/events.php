<?php

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
});
