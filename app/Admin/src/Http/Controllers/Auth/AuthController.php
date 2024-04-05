<?php

namespace App\Admin\Http\Controllers\Auth;

use App\Admin\Http\Requests\Auth\LoginRequest;
use App\Admin\Http\Requests\Auth\LogoutRequest;
use App\Admin\Http\Resources\Auth\AdminResource;
use App\Models\User;
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
    public function login(LoginRequest $request): AdminResource
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole(RoleName::ADMIN)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials'],
            ])->status(403);
        }

        if (Auth::attempt($credentials)) {
            $user->tokens()->where('name', $user->email)->delete();
            return new AdminResource($user);
        }

        throw ValidationException::withMessages([
            'error' => ['Invalid credentials'],
        ])->status(403);

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
