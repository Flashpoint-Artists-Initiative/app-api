<?php

use App\Http\Controllers\Api\UserRolesController;
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
    Orion::belongsToManyResource('users', 'roles', UserRolesController::class)->only(['index', 'attach', 'detach', 'sync']);
});
