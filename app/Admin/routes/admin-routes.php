<?php

use App\Admin\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin'], function () {
    Route::post('/login', [\App\Admin\Http\Controllers\Auth\AuthController::class, 'login'])->middleware(['throttle:admin.login']);

    Route::middleware(['auth:sanctum', 'verified', 'admin'])->group(function () {
        Route::post('/logout', [\App\Admin\Http\Controllers\Auth\AuthController::class, 'logout']);

        Route::apiResource('/users', UserController::class);
    });
});
