<?php

namespace App\Platform\Http\Controllers\Auth\Social;

use App\Framework\Http\Controllers\Controller;
use App\Platform\Http\Resources\Auth\AuthenticatedMemberResource;
use App\Platform\Http\Resources\Auth\RedirectLinkResource;
use App\Platform\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Laravel\Socialite\Facades\Socialite;

class GitHubAuthController extends Controller
{
    #[Endpoint(title: 'GitHub Login Redirect', description: 'This endpoint redirect to the GitHub SignIn Form')]
    #[Group('Platform')]
    public function redirect(): RedirectLinkResource
    {
        return new RedirectLinkResource(Socialite::driver('github')->stateless()->redirect()->getTargetUrl());
    }

    #[Endpoint(title: 'GitHub Login Callback', description: 'This endpoint sign in the users with GitHub Account')]
    #[Group('Platform')]
    public function handleCallback(): AuthenticatedMemberResource
    {
        $githubUser = Socialite::driver('github')->stateless()->user();
        $user = User::where('email', $githubUser->email)->first();

        if (!$user) {
            abort(404, 'Account not found');
        }

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedMemberResource($user, $token);

    }
}
