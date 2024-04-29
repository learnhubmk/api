<?php

use App\Platform\Http\Controllers\Auth\Social\GoogleAuthController;

Route::get('/login/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/login/google/callback', [GoogleAuthController::class, 'handleCallback']);
