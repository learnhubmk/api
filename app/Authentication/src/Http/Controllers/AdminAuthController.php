<?php

namespace App\Authentication\Http\Controllers;

use App\Framework\Models\User;
use App\Framework\Enums\RoleName;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Authenticated;
use Illuminate\Validation\ValidationException;
use App\Authentication\Http\Resources\AuthResource;
use App\Authentication\Http\Requests\AdminLoginRequest;
use App\Authentication\Http\Requests\AdminLogoutRequest;

class AdminAuthController extends Controller
{
    #[Authenticated]
    #[Endpoint(title: 'Admin User', description: 'This endpoint enables to list admin information after login')]
    #[Group('Authentication')]

    public function index(): AuthResource
    {
        return new AuthResource(auth()->user());
    }

    #[Endpoint(title: 'Admin Login', description: 'This endpoint enables users with admin role to sign in')]
    #[Group('Authentication')]
    #[BodyParam('email', 'password', required: true)]

    public function login(AdminLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        $credentials = request(['email', 'password']);

        if (!$user || !$user->hasRole(RoleName::ADMIN) || ! $token = auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }


        return  response()->json([
            'id'            => $user->id,
            'is_verified'   => $user->hasVerifiedEmail(),
            'email'         => $user->email,
            'role'          => $user->getRoleNames(),
            'access_token'  => $token,
            'token_type'    => 'bearer',
            'expires_in'    => auth()->factory()->getTTL() * 60
        ]);
    }

    #[Authenticated]
    #[Endpoint(title: 'Admin Logout', description: 'This endpoint enables users with admin role to log out')]
    #[Group('Authentication')]

    public function logout(AdminLogoutRequest $request)
    {

        auth()->logout();

        return response()->json([
            'message' => __('auth.logout')
        ]);
    }
}
