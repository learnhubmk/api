<?php

use App\Platform\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Platform\Http\Controllers\Auth\UserRegisterController;
use App\Platform\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::as('v1.')
    ->middleware(['throttle:api'])
    ->prefix('api/v1')
    ->group(function () {
        Route::post('/signup', [UserRegisterController::class, 'store'])
            ->name('api.signup');
    });
Route::post(
    '/email/verification-notification',
    [EmailVerificationNotificationController::class, 'store']
)->middleware(['auth', 'throttle:api'])->name('verification.send');

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])->middleware(['auth', 'signed'])->name('verification.verify');
