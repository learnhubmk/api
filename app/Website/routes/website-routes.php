<?php

use Illuminate\Support\Facades\Route;

Route::prefix('blogs')->group(function () {
   Route::get('/list', [\App\Website\Http\Controllers\BlogController::class, 'index']);
});
