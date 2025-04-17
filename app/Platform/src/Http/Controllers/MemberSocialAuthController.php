<?php

namespace App\Platform\Http\Controllers;

use App\Authentication\Http\Resources\AuthenticatedMemberResource;
use App\Authentication\Http\Resources\RedirectLinkResource;
use App\Framework\Enums\RoleName;
use App\Framework\Models\User;
use App\Platform\Models\MemberProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;
use Laravel\Socialite\Facades\Socialite;

class MemberSocialAuthController
{
    #[Group('Platform')]
    #[Endpoint(title: 'Socialite Member Register Redirect', description: 'This endpoint redirects to the Social SignUp Form')]
    #[UrlParam('provider', 'string', required: true, example: 'google', enum: ['github', 'google', 'linkedin'])]
    public function redirect(string $provider): RedirectLinkResource
    {
        $this->validateProvider($provider);

        return new RedirectLinkResource(
            Socialite::driver($provider)
                ->with(['prompt' => 'select_account']) // Modern way to handle stateless login
                ->redirect()
                ->getTargetUrl()
        );
    }

    #[Group('Platform')]
    #[Endpoint(title: 'Socialite Member Register Callback', description: 'This endpoint signs up the users with Google, Github, or LinkedIn Account')]
    #[UrlParam('provider', 'string', required: true, example: 'google', enum: ['github', 'google', 'linkedin'])]
    public function handleCallback(string $provider): AuthenticatedMemberResource|JsonResponse
    {
        $this->validateProvider($provider);

        try {
            $socialiteUser = Socialite::driver($provider)->user();
            $user = User::where('email', $socialiteUser->getEmail())->first();

            if (! $user) {
                $user = new User([
                    'email' => $socialiteUser->getEmail(),
                ]);
                $user->save();

                $user->assignRole(RoleName::MEMBER->value);

                [$firstName, $lastName] = array_pad(explode(' ', $socialiteUser->getName(), 2), 2, null);

                MemberProfile::create([
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'image' => $socialiteUser->getAvatar(),
                ]);
            }

            return new AuthenticatedMemberResource($user);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    protected function validateProvider(string $provider): void
    {
        if (! in_array($provider, ['google', 'github', 'linkedin'])) {
            throw ValidationException::withMessages([
                'error' => __('auth.socialite-provider'),
            ]);
        }
    }
}
