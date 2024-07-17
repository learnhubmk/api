<?php

namespace App\Authentication\Http\Controllers;

use App\Authentication\Http\Resources\AuthenticatedMemberResource;
use App\Authentication\Http\Resources\RedirectLinkResource;
use App\Framework\Enums\RoleName;
use App\Framework\Models\User;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;
use Laravel\Socialite\Facades\Socialite;

class MemberSocialAuthController
{
    #[Group('Authentication')]
    #[Endpoint(title: 'Socialite Register Redirect', description: 'This endpoint redirect to the Social SignUp Form')]
    #[UrlParam('provider', 'string', required: true, example: "google", enum: ["github", "google", "linkedin"])]
    public function redirect($provider)
    {
        $this->validateProvider($provider);

        return new RedirectLinkResource(Socialite::driver($provider)->stateless()->redirect()->getTargetUrl());
    }

    #[Group('Authentication')]
    #[Endpoint(title: 'Socialite Register Callback', description: 'This endpoint sign up the users with Google, Github or LinkedIn Account')]
    #[UrlParam('provider', 'string', required: true, example: "google", enum: ["github", "google", "linkedin"])]
    public function handleCallback($provider)
    {
        $this->validateProvider($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
            $user = User::where('email', $socialiteUser->getEmail())->first();

            if (!$user) {
                $user = new User();
                $user->email = $socialiteUser->getEmail();
                $user->save();

                $user->assignRole(RoleName::MEMBER->value);
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
