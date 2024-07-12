<?php

namespace App\Authentication\Http\Controllers;

use Exception;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\ValidationException;
use App\Authentication\Http\Resources\RedirectLinkResource;
use App\Authentication\Http\Resources\AuthenticatedMemberResource;
use Knuckles\Scribe\Attributes\UrlParam;

class SocialiteAuthController
{
    #[Group('Authenticiation')]
    #[Endpoint(title: 'Socialite Login Redirect', description: 'This endpoint redirect to the Social SignIn Form')]
    #[UrlParam('provider', 'string', required: true, enum: ["github", "google", "linkedin"], example: "google")]
    public function redirect($provider)
    {
        $this->validateProvider($provider);

        return new RedirectLinkResource(Socialite::driver($provider)->stateless()->redirect()->getTargetUrl());
    }

    #[Group('Authenticiation')]
    #[Endpoint(title: 'Socialite Login Callback', description: 'This endpoint sign in the users with Google, Github or LinkedIn Account')]
    #[UrlParam('provider', 'string', required: true, enum: ["github", "google", "linkedin"], example: "google")]
    public function handleCallback($provider)
    {
        $this->validateProvider($provider);

        try {
            $user = Socialite::driver($provider)->stateless()->user();

        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return new AuthenticatedMemberResource($user);
    }


    protected function validateProvider($provider)
    {
        if (!in_array($provider, ['google', 'github', 'linkedin'])) {
            throw ValidationException::withMessages([
                'error' => __('auth.socialite-provider'),
            ]);
        }
    }

}
