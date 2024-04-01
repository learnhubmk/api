<?php

namespace App\Admin\Http\Controllers\Auth;

use App\Admin\Http\Requests\Auth\LoginRequest;
use App\Admin\Http\Requests\Auth\LogoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;

class AuthController
{
    /**
     * @throws ValidationException
     */
    #[Endpoint('Admin/Login', <<<'DESC'
    This endpoint enable users with admin role to sign in
 DESC)]
    #[BodyParam('email', 'password', required: true)]
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = $request->user();
        $token = $user->tokens->where('name', $user->email)->first();

        if ($token) {
            $token->delete();
        }
        $token = $user->createToken($user->email)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);

    }

    #[Endpoint('Admin/Logout', <<<'DESC'
    This endpoint enable users with admin role to log out
 DESC)]
    public function logout(LogoutRequest $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
