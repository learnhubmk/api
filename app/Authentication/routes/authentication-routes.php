<?php

use Illuminate\Support\Facades\Route;
use App\Authentication\Http\Controllers\AdminAuthController;
use App\Authentication\Http\Controllers\MemberAuthController;
use App\Authentication\Http\Controllers\ContentManagerAuthController;
use App\Authentication\Http\Controllers\NewPasswordController;
use App\Authentication\Http\Controllers\PasswordResetLinkController;
use App\Authentication\Http\Controllers\SocialiteAuthController;

Route::group(['middleware' => ['auth:api', 'treblle']], function () {
    /**ADMIN*/
    Route::group(['prefix' => '/admin', 'as' => 'admin.'], function () {
        Route::get('/user', [AdminAuthController::class, 'index'])->name('index');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login')->withoutMiddleware(['auth:api']);
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [AdminAuthController::class, 'refresh'])->name('refresh');

    });

    /**CONTENT MANAGER*/
    Route::group(['prefix' => '/content', 'as' => 'content.'], function () {
        Route::get('/user', [ContentManagerAuthController::class, 'index'])->name('index');
        Route::post('/login', [ContentManagerAuthController::class, 'login'])->name('login')->withoutMiddleware(['auth:api']);
        Route::post('/logout', [ContentManagerAuthController::class, 'logout'])->name('logout');
        Route::post('/refresh', [ContentManagerAuthController::class, 'refresh'])->name('refresh');

    });

    /**MEMBERS*/
    Route::get('/user', [MemberAuthController::class, 'index'])->name('index');
    Route::post('/login', [MemberAuthController::class, 'login'])->name('login')->withoutMiddleware(['auth:api']);
    Route::post('/logout', [MemberAuthController::class, 'logout'])->name('logout');
    Route::post('/refresh', [MemberAuthController::class, 'refresh'])->name('refresh');

    /**SOCALITE AUTH */
    Route::get('/login/{provider}/redirect', [SocialiteAuthController::class, 'redirect'])->withoutMiddleware(['auth:api']);
    Route::get('/login/{provider}/callback', [SocialiteAuthController::class, 'handleCallback'])->withoutMiddleware(['auth:api']);

    /**PASSWORD RESET */
    Route::post('/passwords/request-new', PasswordResetLinkController::class)->withoutMiddleware(['auth:api']);
    Route::post('/passwords/reset', NewPasswordController::class)->withoutMiddleware(['auth:api']);
});
