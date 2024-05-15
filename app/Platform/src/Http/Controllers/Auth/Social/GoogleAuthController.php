<?php

namespace App\Platform\Http\Controllers\Auth\Social;

use App\Http\Controllers\Controller;
use App\Platform\Http\Resources\Auth\AuthenticatedMemberResource;
use App\Platform\Http\Resources\Auth\RedirectLinkResource;
use App\Platform\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    #[Endpoint(title: 'Google Login Redirect', description: 'This endpoint redirect to the Google SignIn Form')]
    #[Group('Platform')]
    public function redirect(): RedirectLinkResource
    {
        return new RedirectLinkResource(Socialite::driver('google')->stateless()->redirect()->getTargetUrl());
    }

    #[Endpoint(title: 'Google Login Callback', description: 'This endpoint sign in the users with Google Account')]
    #[Group('Platform')]
    public function handleCallback(): AuthenticatedMemberResource
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();

        if (!$user) {
            abort(404, 'Account not found');
        }

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedMemberResource($user, $token);

    }

}
