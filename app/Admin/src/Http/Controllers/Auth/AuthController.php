<?php

namespace App\Admin\Http\Controllers\Auth;

use App\Admin\Http\Requests\Auth\LoginRequest;
use App\Admin\Http\Requests\Auth\LogoutRequest;
use App\Platform\Enums\RoleName;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Authenticated;
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
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $user = Auth::user();

            if ($user->hasRole(RoleName::ADMIN)) {
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

            } else {
                throw ValidationException::withMessages([
                    'error' => ['Unauthenticated'],
                ])->status(403);
            }
        }

        throw ValidationException::withMessages([
            'error' => ['Invalid credentials'],
        ]);

    }

    #[Authenticated]
    #[Endpoint('Admin/Logout', <<<'DESC'
    This endpoint enable users with admin role to log out
 DESC)]
    public function logout(LogoutRequest $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
