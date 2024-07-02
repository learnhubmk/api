<?php

namespace App\Content\Http\Controllers\Auth;

use App\Framework\Models\User;
use App\Framework\Enums\RoleName;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Authenticated;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use App\Content\Http\Requests\Auth\LoginRequest;
use App\Content\Http\Requests\Auth\LogoutRequest;
use App\Content\Http\Resources\Auth\AuthenticatedContentManagerResource;

class AuthController
{
    /**
     * @throws ValidationException
     */
    #[Endpoint(title: 'Login', description: 'This endpoint enables sign in for users with roles: content or member')]
    #[Group('Content')]
    #[BodyParam('email', 'password', required: true)]
    public function login(LoginRequest $request): AuthenticatedContentManagerResource
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->hasAnyRole([RoleName::CONTENT_MANAGER, RoleName::MEMBER]) || !Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials'],
            ])->status(Response::HTTP_UNAUTHORIZED);
        }

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedContentManagerResource($user, $token);

    }

    #[Authenticated]
    #[Endpoint(title: 'Logout', description: 'This endpoint enables sign out for users with roles: content or member')]
    #[Group('Content')]
    public function logout(LogoutRequest $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
