<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/website'], function () {
    Route::prefix('blog-posts')->group(function () {
        Route::get('/', [\App\Website\Http\Controllers\BlogPostController::class, 'index']);
        Route::get('/{slug}', [\App\Website\Http\Controllers\BlogPostController::class, 'show']);
    });
});
