<?php

// use App\Content\Http\Controllers\ContentController;
use App\Content\Http\Controllers\Auth\AuthController;

Route::group(['prefix' => '/content'], function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['throttle:admin.login']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });

});


// Route::get('/contents', [ContentController::class, 'index'])->name('contents.index');
// Route::get('/contents/create', [ContentController::class, 'create'])->name('contents.create');
// Route::post('/contents', [ContentController::class, 'store'])->name('contents.store');
// Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');
// Route::get('/contents/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
// Route::put('/contents/{content}', [ContentController::class, 'update'])->name('contents.update');
// Route::delete('/contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');
