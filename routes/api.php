<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/**
 * Healthcheck
 *
 * Check that the service is up. If everything is okay, you'll get a 200 OK response.
 *
 * Otherwise, the request will fail with a 400 error, and a response listing the failed services.
 */
Route::get(
    '/healthcheck',
    #[\Knuckles\Scribe\Attributes\Response(['status' => 'down', 'services' => ['database' => 'up', 'redis' => 'down']], status: 400, description: 'Service is unhealthy')]
    #[\Knuckles\Scribe\Attributes\ResponseField('status', 'The status of this API (`up` or `down`).')]
    #[\Knuckles\Scribe\Attributes\ResponseField('services', 'Map of each downstream service and their status (`up` or `down`).')]
    function () {
        return [
            'status' => 'up',
            'services' => [
                'database' => 'up',
                'redis' => 'up',
            ],
        ];
    }
);

Route::as('v1.')
    ->middleware(['throttle:api'])
    ->prefix('v1')
    ->group(function () {
        Route::post('/signup', [RegisteredUserController::class, 'store'])
            ->name('signup');
    });

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
