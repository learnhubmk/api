<?php

namespace App\Platform\Http\Controllers\Auth\Social;

use App\Http\Controllers\Controller;
use App\Platform\Http\Resources\Auth\AuthenticatedMemberResource;
use App\Platform\Models\User;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleAuthController extends Controller
{
    #[Endpoint(title: 'Redirect', description: 'This endpoint redirect to the Google SignIn Form')]
    #[Group('Platform')]
    public function redirect(): RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    #[Endpoint(title: 'Callback', description: 'This endpoint sign in the users with Google Account')]
    #[Group('Platform')]
    public function handleCallback(): AuthenticatedMemberResource
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->email)->first();

        $user->tokens()->where('name', $user->email)->delete();

        $token = $user->createToken($user->email)->plainTextToken;

        return new AuthenticatedMemberResource($user, $token);

    }

}
