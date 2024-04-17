<?php

use App\Admin\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin'], function () {
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login')->middleware(['throttle:admin.login']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    });

});
