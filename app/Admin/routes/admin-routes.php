<?php

use App\Admin\Http\Controllers\UserController;
use App\Admin\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin', 'middleware' => ['treblle']], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login')->middleware(['throttle:login']);

    Route::middleware(['auth:sanctum', 'verified', 'role:admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::apiResource('/users', UserController::class);
    });
});
