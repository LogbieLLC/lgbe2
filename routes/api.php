<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BanController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\VoteController;
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

// API Authentication Routes
Route::post('/auth/register', [RegisteredUserController::class, 'apiStore']);
Route::post('/auth/login', [AuthenticatedSessionController::class, 'apiStore']);
Route::post('/auth/forgot-password', [PasswordResetLinkController::class, 'apiStore']);
Route::post('/auth/reset-password', [NewPasswordController::class, 'apiStore']);

// Communities
Route::apiResource('communities', CommunityController::class);
Route::post('communities/{community}/join', [CommunityController::class, 'join'])->middleware(['auth:sanctum', \App\Http\Middleware\CheckBanned::class]);
Route::post('communities/{community}/leave', [CommunityController::class, 'leave'])->middleware(['auth:sanctum', \App\Http\Middleware\CheckBanned::class]);

// Posts
Route::apiResource('communities.posts', PostController::class)
    ->middleware(['auth:sanctum', \App\Http\Middleware\CheckBanned::class], ['only' => ['store']])
    ->shallow();
Route::delete('communities/{community}/posts/{post}', [PostController::class, 'removeFromCommunity'])->middleware('auth:sanctum');

// Comments
Route::apiResource('posts.comments', CommentController::class)->shallow();
Route::apiResource('comments.replies', CommentController::class)->shallow();

// Votes
Route::post('posts/{post}/vote', [VoteController::class, 'votePost'])->middleware('auth:sanctum');
Route::post('comments/{comment}/vote', [VoteController::class, 'voteComment'])->middleware('auth:sanctum');

// Bans
Route::post('communities/{community}/ban/{user}', [BanController::class, 'store'])->middleware('auth:sanctum');
Route::delete('communities/{community}/ban/{user}', [BanController::class, 'destroy'])->middleware('auth:sanctum');
