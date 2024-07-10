<?php

namespace App\Authentication\Http\Controllers;

use App\Framework\Models\User;
use Illuminate\Routing\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Authenticated;
use App\Authentication\Http\Resources\AuthResource;
use App\Authentication\Http\Requests\ContentManagerLoginRequest;
use App\Authentication\Http\Requests\ContentManagerLogoutRequest;

class ContentManagerAuthController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'Index', description: 'This endpoint enables to list content manager information after login')]
    #[Group('Content')]

    public function index(): AuthResource
    {
        return new AuthResource(auth()->user());
    }
    #[Endpoint(title: 'Login', description: 'This endpoint enables users with content role to sign in')]
    #[Group('Content')]
    #[BodyParam('email', 'password', required: true)]

    public function login(ContentManagerLoginRequest $request): AuthResource
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate($user);

        return new AuthResource($user);
    }

    #[Authenticated]
    #[Endpoint(title: 'Logout', description: 'This endpoint enables users with content role to log out')]
    #[Group('Content')]

    public function logout(ContentManagerLogoutRequest $request)
    {

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => __('auth.logout')
        ]);
    }
}
