<?php

use App\Content\Http\Controllers\BlogPostController;
use App\Content\Http\Controllers\BlogPostStatusController;
use App\Content\Http\Controllers\BlogPostTagsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/content', 'middleware' => ['auth:api', 'treblle']], function () {
    Route::resource('/blog-posts', BlogPostController::class)->except('create', 'edit', 'update');
    Route::post('/blog-posts/{blogPost}', [BlogPostController::class , 'update']);
    Route::resource('/blog-post-tags', BlogPostTagsController::class)->only('index', 'store', 'update', 'destroy')
        ->name('index', 'content.blog-post-tags.index')
        ->name('show', 'content.blog-post-tags.show')
        ->name('store', 'content.blog-post-tags.store')
        ->name('update', 'content.blog-post-tags.update')
        ->name('destroy', 'content.blog-post-tags.destroy');
    Route::patch('blog-posts/{id}/statuses', [BlogPostStatusController::class, 'update']);
});
