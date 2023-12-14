<?php

use App\Http\Controllers\Api\UsersController;
use Illuminate\Support\Facades\Route;
use Orion\Facades\Orion;

/*
|--------------------------------------------------------------------------
| Users Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->as('api.')->group(function () {
    Orion::resource('users', UsersController::class)->withoutBatch();
});
