<?php

use App\Platform\Http\Controllers\MemberSocialAuthController;
use App\Platform\Http\Controllers\RegisterMemberController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api', 'treblle']], function () {

    Route::post('/register', [RegisterMemberController::class, 'store'])->withoutMiddleware(['auth:api']);

    Route::get('/register/{provider}/redirect', [MemberSocialAuthController::class, 'redirect'])->withoutMiddleware(['auth:api']);
    Route::get('/register/{provider}/callback', [MemberSocialAuthController::class, 'handleCallback'])->withoutMiddleware(['auth:api']);

});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json([
        'message' => 'Email verified successfully',
        'status' => 200
    ]);
})->middleware(['guest', 'signed', 'api'])->name('verification.verify');
