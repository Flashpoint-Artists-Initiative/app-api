<?php

use App\Http\Controllers\Api\Me\TicketsController;
use App\Http\Controllers\Api\MeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| (User) Me Routes
|--------------------------------------------------------------------------
*/
Route::controller(MeController::class)->middleware(['auth'])->prefix('me')->as('api.me.')->group(function () {
    Route::get('/', 'indexAction')->name('index');
    Route::match(['PUT', 'PATCH'], '/', 'update')->name('update');
    Route::get('/tickets', 'ticketsAction')->name('tickets');
    Route::get('/orders', 'ordersAction')->name('orders');
    Route::get('/waivers', 'waiversAction')->name('waivers');
    Route::post('/tickets/transfer', [TicketsController::class, 'transferAction'])->name('tickets.transfer');
});
