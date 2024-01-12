<?php

use App\Http\Controllers\Api\CartController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->controller(CartController::class)->as('api.cart.')->group(function () {
    Route::get('/cart', 'indexAction')->name('index');
    Route::post('/cart', 'createAction')->name('store');
    Route::delete('/cart', 'deleteAction')->name('destroy');
});
