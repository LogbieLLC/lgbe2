<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API authentication routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

Route::post('/auth/register', [RegisteredUserController::class, 'apiStore']);
Route::post('/auth/login', [AuthenticatedSessionController::class, 'apiStore']);
Route::post('/auth/forgot-password', [PasswordResetLinkController::class, 'apiStore']);
Route::post('/auth/reset-password', [NewPasswordController::class, 'apiStore']);
