<?php

namespace App\Authentication\Http\Controllers;

use Exception;
use App\Framework\Models\User;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\UrlParam;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Validation\ValidationException;
use App\Authentication\Http\Resources\RedirectLinkResource;
use App\Authentication\Http\Resources\AuthenticatedMemberResource;

class SocialiteAuthController
{
    #[Group('Authentication')]
    #[Endpoint(title: 'Socialite Login Redirect', description: 'This endpoint redirect to the Social SignIn Form')]
    #[UrlParam('provider', 'string', required: true, enum: ["github", "google", "linkedin"], example: "google")]
    public function redirect($provider)
    {
        $this->validateProvider($provider);

        return new RedirectLinkResource(Socialite::driver($provider)->stateless()->redirect()->getTargetUrl());
    }

    #[Group('Authentication')]
    #[Endpoint(title: 'Socialite Login Callback', description: 'This endpoint sign in the users with Google, Github or LinkedIn Account')]
    #[UrlParam('provider', 'string', required: true, enum: ["github", "google", "linkedin"], example: "google")]
    public function handleCallback($provider)
    {
        $this->validateProvider($provider);

        try {
            $sociliteUser = Socialite::driver($provider)->stateless()->user();
            $user = User::where('email', $sociliteUser->email)->first();

            if (!$user) {
                abort(404, __('auth.failed'));
            }

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
