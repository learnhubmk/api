<?php

use App\Platform\Http\Controllers\MemberSocialAuthController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', 'treblle', 'stateful']], function () {

    Route::get('/register/{provider}/redirect', [MemberSocialAuthController::class, 'redirect'])->withoutMiddleware(['auth:sanctum']);
    Route::get('/register/{provider}/callback', [MemberSocialAuthController::class, 'handleCallback'])->withoutMiddleware(['auth:sanctum']);
});
