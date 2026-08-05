<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessApiController;
use App\Http\Controllers\Api\ErrorLogApiController;
use App\Http\Controllers\Api\StatsApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Direct Data Collection API Routes (No API Token Required)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public authentication / status endpoints
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me']);

    // Business endpoints (Rate limited to 60 req/min for bulk upload)
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('/businesses/bulk', [BusinessApiController::class, 'bulk']);
    });

    Route::get('/businesses', [BusinessApiController::class, 'index']);
    Route::get('/businesses/{id}', [BusinessApiController::class, 'show']);
    Route::delete('/businesses/{id}', [BusinessApiController::class, 'destroy']);
    Route::post('/businesses/check-duplicates', [BusinessApiController::class, 'checkDuplicates']);

    // Metrics & Statistics
    Route::get('/stats', [StatsApiController::class, 'index']);

    // Remote Error Logging
    Route::post('/errors', [ErrorLogApiController::class, 'store']);
});
