<?php

use App\Content\Http\Controllers\Auth\AuthController;

Route::group(['prefix' => '/content'], function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:login']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});
