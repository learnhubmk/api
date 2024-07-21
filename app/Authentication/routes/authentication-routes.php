<?php

use Illuminate\Support\Facades\Route;
use App\Authentication\Http\Controllers\AdminAuthController;
use App\Authentication\Http\Controllers\MemberAuthController;
use App\Authentication\Http\Controllers\ContentManagerAuthController;
use App\Authentication\Http\Controllers\SocialiteAuthController;

Route::group(['middleware' => ['auth:api', 'treblle']], function () {
    /**ADMIN*/
    Route::group(['prefix' => '/admin', 'as' => 'admin.'], function () {
        Route::get('/user', [AdminAuthController::class, 'index'])->name('index');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login')->withoutMiddleware(['auth:api']);
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    });

    /**CONTENT MANAGER*/
    Route::group(['prefix' => '/content', 'as' => 'content.'], function () {
        Route::get('/user', [ContentManagerAuthController::class, 'index'])->name('index');
        Route::post('/login', [ContentManagerAuthController::class, 'login'])->name('login')->withoutMiddleware(['auth:api']);
        Route::post('/logout', [ContentManagerAuthController::class, 'logout'])->name('logout');

    });

    /**MEMBERS*/
    Route::get('/user', [MemberAuthController::class, 'index'])->name('index');
    Route::post('/login', [MemberAuthController::class, 'login'])->name('login')->withoutMiddleware(['auth:sanctum']);
    Route::post('/logout', [MemberAuthController::class, 'logout'])->name('logout');

    /**SOCALITE AUTH */
    Route::get('/login/{provider}/redirect', [SocialiteAuthController::class, 'redirect'])->withoutMiddleware(['auth:sanctum']);
    Route::get('/login/{provider}/callback', [SocialiteAuthController::class, 'handleCallback'])->withoutMiddleware(['auth:sanctum']);
});
