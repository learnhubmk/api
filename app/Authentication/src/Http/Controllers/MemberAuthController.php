<?php

namespace App\Authentication\Http\Controllers;

use App\Framework\Models\User;
use Illuminate\Routing\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Authenticated;
use App\Authentication\Http\Resources\AuthResource;
use App\Authentication\Http\Requests\MemberLoginRequest;
use App\Authentication\Http\Requests\MemberLogoutRequest;

class MemberAuthController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'Member User', description: 'This endpoint enables member information after login')]
    #[Group('Authenticiation')]

    public function index(): AuthResource
    {
        return new AuthResource(auth()->user());
    }

    #[Endpoint(title: 'Member Login', description: 'This endpoint enables users with member role to sign in')]
    #[Group('Authenticiation')]
    #[BodyParam('email', 'password', required: true)]

    public function login(MemberLoginRequest $request): AuthResource
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate($user);

        return new AuthResource($user);
    }

    #[Authenticated]
    #[Endpoint(title: 'Memeber Logout', description: 'This endpoint enables users with Member role to log out')]
    #[Group('Authenticiation')]

    public function logout(MemberLogoutRequest $request)
    {

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => __('auth.logout')
        ]);
    }
}
