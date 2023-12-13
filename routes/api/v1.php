<?php

use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(base_path('routes/api/v1/auth.php'));

Route::fallback(function () {
    return response()->json(['error' => 'Not Found'], 404);
});
