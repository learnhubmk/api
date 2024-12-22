<?php

use App\Admin\Http\Controllers\AdminManagementController;
use App\Admin\Http\Controllers\ContentManagerManagementController;
use App\Admin\Http\Controllers\MemberManagementController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/admin', 'middleware' => ['treblle', 'auth:api', 'verified', 'role:admin']], function () {
    Route::apiResource('/administrators', AdminManagementController::class);
    Route::apiResource('/members', MemberManagementController::class);
    Route::apiResource('/content-managers', ContentManagerManagementController::class);

    Route::patch('/administrators/{id}/restore', [AdminManagementController::class, 'restore']);
    Route::patch('/members/{id}/restore', [MemberManagementController::class, 'restore']);
    Route::patch('/content-managers/{id}/restore', [ContentManagerManagementController::class, 'restore']);
});
