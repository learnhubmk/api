<?php

namespace App\Authentication\Http\Controllers;

use App\Authentication\Http\Resources\AuthenticatedMemberResource;
use App\Authentication\Http\Resources\RedirectLinkResource;
use App\Framework\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class SocialiteAuthController
{
    #[Group('Authentication')]
    #[Endpoint(title: 'Socialite Login Redirect', description: 'This endpoint redirects to the Social Sign-In Form')]
    #[UrlParam('provider', 'string', required: true, enum: ['github', 'google', 'linkedin'], example: 'google')]
    public function redirect(string $provider): RedirectLinkResource
    {
        $this->validateProvider($provider);

        /** @var RedirectResponse $redirect */
        $redirect = Socialite::driver($provider)->redirect();

        return new RedirectLinkResource($redirect->getTargetUrl());
    }

    #[Group('Authentication')]
    #[Endpoint(title: 'Socialite Login Callback', description: 'This endpoint signs in the user with a Google, Github, or LinkedIn account')]
    #[UrlParam('provider', 'string', required: true, enum: ['github', 'google', 'linkedin'], example: 'google')]
    public function handleCallback(string $provider): JsonResponse|AuthenticatedMemberResource
    {
        $this->validateProvider($provider);

        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = Socialite::driver($provider)->user();
            $user = User::where('email', $socialiteUser->getEmail())->first();

            if (! $user) {
                return response()->json(['error' => __('auth.failed')], 404);
            }

            $token = auth()->login($user);

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return AuthenticatedMemberResource::make($user)->additional([
            'access_token' => $token,
        ]);
    }

    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['google', 'github', 'linkedin'], true)) {
            throw ValidationException::withMessages([
                'error' => __('auth.socialite-provider'),
            ]);
        }
    }
}
