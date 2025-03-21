<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\PerformanceDashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'protect.superadmin'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Community routes
Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/communities/create', [CommunityController::class, 'create'])->middleware(['auth'])->name('communities.create');
Route::post('/communities', [CommunityController::class, 'store'])->middleware(['auth'])->name('communities.store');
Route::get('/communities/{community:slug}', [CommunityController::class, 'show'])->name('communities.show');

// Performance dashboard routes
Route::middleware(['auth', 'performance.dashboard'])->prefix('performance')->group(function () {
    Route::get('/', [PerformanceDashboardController::class, 'index'])->name('performance.dashboard');
    Route::get('/page/{urlPath}', [PerformanceDashboardController::class, 'pageDetails'])->name('performance.page');
});

require __DIR__.'/auth.php';
