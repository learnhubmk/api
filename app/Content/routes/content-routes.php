<?php

use App\Content\Http\Controllers\Auth\AuthController;
use App\Content\Http\Controllers\BlogPostController;
use App\Content\Http\Controllers\BlogPostTagsController;

Route::group(['prefix' => '/content', 'middleware' => ['treblle']], function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:login']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
<<<<<<< HEAD

        Route::group(['prefix' => '/blog-posts'], function () {
            Route::get('/', [BlogPostController::class, 'index']);
            Route::post('/', [BlogPostController::class, 'store']);
            Route::get('/{blogPost}', [BlogPostController::class, 'show']);
            Route::patch('/{blogPost}', [BlogPostController::class, 'update']);
            Route::delete('/{blogPost}', [BlogPostController::class, 'destroy']);
            Route::get('/changeStatus/{blogPost}', [BlogPostController::class, 'changeStatus']);
        });

        Route::group(['prefix' => '/blog-posts-tags'], function () {
            Route::post('/', [BlogPostTagsController::class, 'index']);
            Route::post('/', [BlogPostTagsController::class, 'store']);
            Route::patch('/{blogPostTag}', [BlogPostTagsController::class, 'update']);
            Route::delete('/{blogPostTag}', [BlogPostTagsController::class, 'destroy']);
        });
=======
>>>>>>> refs/remotes/origin/main
    });
});
