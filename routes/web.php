<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Home page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Communities
Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/communities/create', [CommunityController::class, 'create'])->middleware('auth')->name('communities.create');
Route::get('/communities/{community}', [CommunityController::class, 'show'])->middleware(\App\Http\Middleware\CheckBanned::class)->name('communities.show');
Route::get('/communities/{community}/edit', [CommunityController::class, 'edit'])->middleware(['auth', \App\Http\Middleware\CheckBanned::class])->name('communities.edit');

// Posts
Route::get('/communities/{community}/posts/create', [PostController::class, 'create'])->middleware(['auth', \App\Http\Middleware\CheckBanned::class])->name('posts.create');
Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->middleware('auth')->name('posts.edit');

// User profiles
Route::get('/u/{user}', [ProfileController::class, 'show'])->name('profile.show');

// Include other route files
require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/api.php';
