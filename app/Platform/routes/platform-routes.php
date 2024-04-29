<?php

use App\Platform\Http\Controllers\Auth\Social\GoogleAuthController;

Route::group(['prefix' => '/platform'], function () {
    Route::group(['prefix' => '/social'], function () {
        Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
        Route::get('/google/callback', [GoogleAuthController::class, 'handleCallback']);
    });
});
