<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin'], function () {
   Route::post('/login', [\App\Admin\Http\Controllers\Auth\AuthController::class, 'login']);
});
