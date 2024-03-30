<?php

use App\Website\Http\Controllers\BlogPostController;
use App\Website\Http\Controllers\BlogPostTagsController;
use Illuminate\Support\Facades\Route;

Route::get('blog-posts', [BlogPostController::class, 'index']);
Route::get('blog-posts/{slug}', [BlogPostController::class, 'show']);

Route::get('/blog-post-tags', [BlogPostTagsController::class, 'index']);
Route::get('/blog-post-tags/{tag}', [BlogPostTagsController::class, 'show']);
