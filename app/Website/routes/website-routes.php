<?php

use Illuminate\Support\Facades\Route;
use App\Website\Http\Controllers\HomeController;
use App\Website\Http\Controllers\ContactController;
use App\Website\Http\Controllers\BlogPostController;
use App\Website\Http\Controllers\BlogPostTagsController;
use App\Website\Http\Controllers\NewsletterSubscriberController;

Route::get('/', HomeController::class)->name('home');

Route::get('blog-posts', [BlogPostController::class, 'index'])->name('blog-post.index');
Route::get('blog-posts/{slug}', [BlogPostController::class, 'show'])->name('blog-post.show');

Route::get('/blog-post-tags', [BlogPostTagsController::class, 'index'])->name('blog-post-tags.index');
Route::get('/blog-post-tags/{tag}', [BlogPostTagsController::class, 'show'])->name('blog-post-tags.show');

Route::post('/contact', ContactController::class)->name('contact')->middleware('throttle:5,1');

Route::post('newsletter-subscribers', [NewsletterSubscriberController::class, 'store'])->middleware('throttle:5,1');
