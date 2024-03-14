<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api/website'], function () {
    Route::prefix('blogs')->group(function () {
        Route::get('/list', [\App\Website\Http\Controllers\BlogController::class, 'index']);
    });
});

