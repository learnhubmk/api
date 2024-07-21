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

class MemberAuthController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'Member User', description: 'This endpoint enables member information after login')]
    #[Group('Authentication')]

    public function index(): AuthResource
    {
        return new AuthResource(auth()->user());
    }

    #[Endpoint(title: 'Member Login', description: 'This endpoint enables users with member role to sign in')]
    #[Group('Authentication')]
    #[BodyParam('email', 'password', required: true)]

    public function login(MemberLoginRequest $request): AuthResource
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate($user);

        $token = auth()->login($user);

        return  AuthResource::make($user)->additional([
                        'access_token'  => $token,
                    ]);
    }

    #[Authenticated]
    #[Endpoint(title: 'Memeber Logout', description: 'This endpoint enables users with Member role to log out')]
    #[Group('Authentication')]

    public function logout()
    {

        auth()->logout();

        return response()->json([
            'message' => __('auth.logout')
        ]);
    }

    #[Authenticated]
    #[Endpoint(title: 'Member Refresh Token', description: 'This endpoint will delete old and create new token')]
    #[Group('Authentication')]
    public function refresh()
    {

        $newToken = auth()->refresh();

        return response()->json([
            'message' => __('auth.refresh'),
            'new_token' => $newToken
        ]);
    }

}
