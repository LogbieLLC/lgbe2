<?php

use App\Http\Controllers\MetricsController;
use App\Http\Controllers\PerformanceDashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Search routes
Route::get('/search', [SearchController::class, 'search']);

// User routes
Route::get('/users/{user}', [UserController::class, 'show']);

// Performance metrics collection endpoint - public but rate limited
Route::post('/metrics', [MetricsController::class, 'store'])
    ->middleware('throttle:60,1'); // Limit to 60 requests per minute

// Performance dashboard API routes - protected
Route::middleware(['auth:sanctum', 'performance.dashboard'])->prefix('performance')->group(function () {
    Route::get('/trends', [PerformanceDashboardController::class, 'getTrends']);
    Route::get('/reports/download', [PerformanceDashboardController::class, 'downloadReport']);
});
