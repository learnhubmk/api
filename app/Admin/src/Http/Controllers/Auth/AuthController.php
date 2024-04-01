<?php

namespace App\Admin\Http\Controllers\Auth;

use App\Admin\Http\Requests\Auth\LoginRequest;
use App\Admin\Http\Resources\Auth\AdminResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\QueryParam;

class AuthController
{
    /**
     * @throws ValidationException
     */
    #[Endpoint('Admin/Login', <<<'DESC'
    This endpoint enable users with admin role to sign in
 DESC)]
    #[BodyParam('email', 'password', required: true)]
    public function login(LoginRequest $request): AdminResource
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->hasRole('admin')) {
                return new AdminResource($user);
            } else {
                throw ValidationException::withMessages([
                    'error' => ['Unauthenticated'],
                ])->status(403);
            }
        }

        throw ValidationException::withMessages([
            'error' => ['Invalid credentials'],
        ]);

    }
}
