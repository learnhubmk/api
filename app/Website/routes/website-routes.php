<?php

use App\Website\Http\Controllers\BlogPostController;
use App\Website\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/website'], function () {
    Route::prefix('blog-posts')->group(function () {
        Route::get('/', [BlogPostController::class, 'index']);
        Route::get('/{slug}', [BlogPostController::class, 'show']);
    });

    Route::get('/tags', [\App\Website\Http\Controllers\BlogTagsController::class, 'index']);
    Route::get('/blog-post-tags/{tag}', [BlogPostController::class, 'listByTag']);
    Route::post('/contact', [ContactController::class, 'sendContactEmail'])->middleware('throttle:5,1');
});
