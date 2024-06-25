<?php

namespace App\Website\Http\Controllers;

use App\Framework\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'name' => 'LearnHub.mk API',
            'version' => 1.0,
            'message' => 'Welcome to the LearnHub.mk API!'
        ]);
    }
}
