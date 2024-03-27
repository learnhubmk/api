<?php

use App\Website\Http\Controllers\BlogPostController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/website'], function () {
    Route::prefix('blog-posts')->group(function () {
        Route::get('/', [BlogPostController::class, 'index']);
        Route::get('/{slug}', [BlogPostController::class, 'show']);
        Route::get('/tag/{tag}', [BlogPostController::class, 'listByTag']);
    });

    Route::get('/tags', [\App\Website\Http\Controllers\BlogTagsController::class, 'index']);
});
