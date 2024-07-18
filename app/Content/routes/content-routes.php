<?php

use App\Content\Http\Controllers\BlogPostController;
use App\Content\Http\Controllers\BlogPostStatusController;
use App\Content\Http\Controllers\BlogPostTagsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/content', 'middleware' => ['auth:sanctum', 'treblle', 'stateful', 'role:content_manager']], function () {
    Route::resource('/blog-posts', BlogPostController::class)->except('create', 'edit');
    Route::resource('/blog-post-tags', BlogPostTagsController::class)->only('index', 'store', 'update', 'destroy');
    Route::patch('blog-posts/{id}/statuses', [BlogPostStatusController::class, 'update']);
});
