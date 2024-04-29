<?php

use App\Platform\Http\Controllers\Auth\Social\AuthGoogleController;

Route::group(['prefix' => '/platform'], function () {
    Route::group(['prefix' => '/social'], function () {
        Route::get('/google/redirect', [AuthGoogleController::class, 'redirect']);
        Route::get('/google/callback', [AuthGoogleController::class, 'handleCallback']);
    });
});
