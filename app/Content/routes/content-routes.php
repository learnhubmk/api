<?php

use App\Content\Http\Controllers\Auth\AuthController;
use App\Content\Http\Controllers\BlogPostController;

Route::group(['prefix' => '/content'], function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:login']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::group(['prefix' => '/blog-posts'], function () {
            Route::get('/', [BlogPostController::class, 'index']);
            Route::post('/', [BlogPostController::class, 'store']);
            Route::get('/{blogPost}', [BlogPostController::class, 'show']);
            Route::patch('/{blogPost}', [BlogPostController::class, 'update']);
            Route::delete('/{blogPost}', [BlogPostController::class, 'destroy']);
        });
    });

});
