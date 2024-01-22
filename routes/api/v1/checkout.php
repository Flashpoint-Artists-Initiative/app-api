<?php

use App\Http\Controllers\Api\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'token.refresh'])->controller(CheckoutController::class)->as('api.checkout.')->group(function () {
    Route::get('/checkout', 'indexAction')->name('index');
    Route::post('/checkout', 'createGeneralSaleAction')->name('store');
    Route::post('/checkout-reserved', 'createReservedAction')->name('store-reserved');
    Route::delete('/checkout', 'deleteAction')->name('destroy');
});
