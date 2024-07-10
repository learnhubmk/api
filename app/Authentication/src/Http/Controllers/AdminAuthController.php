<?php

namespace App\Authentication\Http\Controllers;

use App\Framework\Models\User;
use Illuminate\Routing\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Authenticated;
use App\Authentication\Http\Resources\AuthResource;
use App\Authentication\Http\Requests\AdminLoginRequest;
use App\Authentication\Http\Requests\AdminLogoutRequest;


class AdminAuthController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'Index', description: 'This endpoint enables to list admin information after login')]
    #[Group('Admin')]

    public function index():AuthResource
    {
        return new AuthResource(auth()->user());
    }

    #[Endpoint(title: 'Login', description: 'This endpoint enables users with admin role to sign in')]
    #[Group('Admin')]
    #[BodyParam('email', 'password', required: true)]

    public function login(AdminLoginRequest $request): AuthResource
    {
        $user = User::where('email', $request->email)->first();

        $request->authenticate($user);

        return new AuthResource($user);
    }

    #[Authenticated]
    #[Endpoint(title: 'Logout', description: 'This endpoint enables users with admin role to log out')]
    #[Group('Admin')]

    public function logout(AdminLogoutRequest $request)
    {

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => __('auth.logout')
        ]);
    }
}
