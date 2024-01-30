<?php

use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Middleware\StripeWebhookMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/api/v1/auth.php'));
Route::group([], base_path('routes/api/v1/users.php'));
Route::group([], base_path('routes/api/v1/events.php'));
Route::group([], base_path('routes/api/v1/ticket-types.php'));
Route::group([], base_path('routes/api/v1/checkout.php'));
Route::group([], base_path('routes/api/v1/me.php'));

Route::middleware([StripeWebhookMiddleware::class])->post('/stripe', [StripeWebhookController::class, 'webhookAction'])->name('api.stripe-webhook');

Route::fallback(function () {
    return response()->json(['error' => 'Not Found'], 404);
});
