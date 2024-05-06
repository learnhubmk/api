<?php

namespace App\Admin\Http\Controllers\Auth;

use App\Admin\Http\Requests\Auth\LoginRequest;
use App\Admin\Http\Requests\Auth\LogoutRequest;
use App\Admin\Http\Resources\Auth\AuthenticatedAdminResource;
use App\Admin\Models\User;
use App\Admin\Enums\RoleName;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

class AuthController
{
    /**
     * @throws ValidationException
     */
    #[Endpoint(title: 'Login', description: 'This endpoint enables users with admin role to sign in')]
    #[Group('Admin')]
    #[BodyParam('email', 'password', required: true)]
    public function login(LoginRequest $request): AuthenticatedAdminResource
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole(RoleName::ADMIN) || !Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials'],
            ])->status(403);
        }

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedAdminResource($user, $token);

    }

    #[Authenticated]
    #[Endpoint(title: 'Logout', description: 'This endpoint enables users with admin role to log out')]
    #[Group('Admin')]
    public function logout(LogoutRequest $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
