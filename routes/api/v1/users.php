<?php

use App\Http\Controllers\Api\Users\RolesController;
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
    Orion::belongsToManyResource('users', 'roles', RolesController::class)->only(['attach', 'detach', 'sync']);
});
