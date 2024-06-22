<?php

use App\Admin\Http\Controllers\AdminManagementController;
use App\Admin\Http\Controllers\ContentManagerManagementController;
use App\Admin\Http\Controllers\MemberManagementController;
use App\Admin\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin', 'middleware' => ['treblle']], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login')->middleware(['throttle:login']);

    Route::middleware(['auth:sanctum', 'verified', 'role:admin'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::apiResource('/admins', AdminManagementController::class);
        Route::apiResource('/members', MemberManagementController::class);
        Route::apiResource('/content-managers', ContentManagerManagementController::class);
    });
});
