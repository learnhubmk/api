<?php

namespace App\Content\Http\Controllers\Auth;

use App\Content\Http\Requests\Auth\LoginRequest;
use App\Content\Http\Requests\Auth\LogoutRequest;
use App\Content\Http\Resources\Auth\AuthenticatedContentManagerResource;
use App\Models\User;
use App\Enums\RoleName;
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
    #[Endpoint(title: 'Login', description: 'This endpoint enables users with content role to sign in')]
    #[Group('Content')]
    #[BodyParam('email', 'password', required: true)]
    public function login(LoginRequest $request): AuthenticatedContentManagerResource
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasRole(RoleName::CONTENT_MANAGER) || !Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials'],
            ])->status(403);
        }

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedContentManagerResource($user, $token);

    }

    #[Authenticated]
    #[Endpoint(title: 'Logout', description: 'This endpoint enables users with content role to log out')]
    #[Group('Content')]
    public function logout(LogoutRequest $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
