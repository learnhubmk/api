<?php

use App\Admin\Http\Controllers\AdminManagementController;
use App\Admin\Http\Controllers\ContentManagerManagementController;
use App\Admin\Http\Controllers\MemberManagementController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin', 'middleware' => ['treblle']], function () {
    Route::middleware(['auth:sanctum', 'verified', 'role:admin'])->group(function () {
        Route::apiResource('/admins', AdminManagementController::class);
        Route::apiResource('/members', MemberManagementController::class);
        Route::apiResource('/content-managers', ContentManagerManagementController::class);
    });
});
