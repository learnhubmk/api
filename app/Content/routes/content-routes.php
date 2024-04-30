<?php

use App\Content\Http\Controllers\Auth\AuthController;
use App\Content\Http\Controllers\BlogPostController;

Route::group(['prefix' => '/content'], function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:login']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::apiResources([
            'blog-posts' => BlogPostController::class
        ]);
    });

});
