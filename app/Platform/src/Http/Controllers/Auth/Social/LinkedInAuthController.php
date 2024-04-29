<?php

namespace App\Platform\Http\Controllers\Auth\Social;

use App\Http\Controllers\Controller;
use App\Platform\Models\User;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LinkedInAuthController extends Controller
{
    #[Endpoint(title: 'Redirect', description: 'This endpoint redirect to the LinkedIn SignIn Form')]
    #[Group('Platform')]
    public function redirect(): RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('linkedin')->stateless()->redirect();
    }

    #[Endpoint(title: 'Callback', description: 'This endpoint sign in the users with LinkedIn Account')]
    #[Group('Platform')]
    public function handleCallback(): AuthenticatedMemberResource
    {
        $linkedinUser = Socialite::driver('linkedin')->stateless()->user();
        $user = User::where('email', $linkedinUser->email)->first();

        if (!$user) {
            abort(404, 'Account not found');
        }

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedMemberResource($user, $token);

    }
}
