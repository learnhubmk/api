<?php

use App\Platform\Http\Controllers\Auth\UserRegisterController;
use Illuminate\Support\Facades\Route;

Route::as('v1.')
    ->middleware(['throttle:api'])
    ->prefix('api/v1')
    ->group(function () {
        Route::post('/signup', [UserRegisterController::class, 'store'])
            ->name('signup');
    });
