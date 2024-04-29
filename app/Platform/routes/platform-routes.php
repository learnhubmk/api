<?php

use App\Platform\Http\Controllers\Auth\Social\GoogleAuthController;
use App\Platform\Http\Controllers\Auth\Social\GitHubAuthController;
use App\Platform\Http\Controllers\Auth\Social\LinkedInAuthController;

Route::get('/login/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/login/google/callback', [GoogleAuthController::class, 'handleCallback']);
Route::get('/login/github/redirect', [GitHubAuthController::class, 'redirect']);
Route::get('/login/github/callback', [GitHubAuthController::class, 'handleCallback']);
Route::get('/login/linkedin/redirect', [LinkedInAuthController::class, 'redirect']);
Route::get('/login/linkedin/callback', [LinkedInAuthController::class, 'handleCallback']);
