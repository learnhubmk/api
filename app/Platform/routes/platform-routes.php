<?php

use App\Platform\Http\Controllers\Auth\Social\GoogleAuthController;

Route::get('/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/google/callback', [GoogleAuthController::class, 'handleCallback']);
