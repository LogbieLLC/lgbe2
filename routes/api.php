<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\PerformanceDashboardController;
use App\Http\Controllers\PostController;
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

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

// Search routes
Route::get('/search', [SearchController::class, 'search']);
Route::prefix('search')->group(function () {
    Route::get('/posts', [SearchController::class, 'searchPosts']);
    Route::get('/communities', [SearchController::class, 'searchCommunities']);
    Route::get('/comments', [SearchController::class, 'searchComments']);
});

// User routes
Route::get('/users/{user}', [UserController::class, 'show']);
Route::put('/users/{user}', [UserController::class, 'update']);
Route::prefix('users/{user}')->group(function () {
    Route::get('/posts', [UserController::class, 'posts']);
    Route::get('/comments', [UserController::class, 'comments']);
});

// Post and comment routes
Route::prefix('posts/{post}')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
    Route::get('/comments', [CommentController::class, 'index']);
    Route::post('/vote', [PostController::class, 'vote'])->middleware('auth:sanctum');
});

// Get post details with vote count
Route::get('/posts/{post}', [PostController::class, 'show']);

Route::prefix('comments/{comment}')->group(function () {
    Route::put('/', [CommentController::class, 'update']);
    Route::delete('/', [CommentController::class, 'destroy']);
});

// Performance metrics collection endpoint - public but rate limited
Route::post('/metrics', [MetricsController::class, 'store'])
    ->middleware('throttle:60,1'); // Limit to 60 requests per minute

// Performance dashboard API routes - protected
Route::middleware(['auth:sanctum', 'performance.dashboard'])->prefix('performance')->group(function () {
    Route::get('/trends', [PerformanceDashboardController::class, 'getTrends']);
    Route::get('/reports/download', [PerformanceDashboardController::class, 'downloadReport']);
});
