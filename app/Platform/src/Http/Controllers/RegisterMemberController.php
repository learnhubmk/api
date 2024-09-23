<?php

namespace App\Platform\Http\Controllers;

use App\Framework\Enums\RoleName;
use App\Framework\Models\User;
use App\Platform\Http\Requests\RegisterRequest;
use App\Platform\Http\Resources\MemberProfileResource;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterMemberController
{
    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(RegisterRequest $request)
    {
        $user = User::query()->create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole(RoleName::MEMBER->value);

        $user->memberProfile()->create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'image' => $request->image,
        ]);

        event(new Registered($user));

        $token = auth()->login($user);

        return MemberProfileResource::make($user)->additional([
            'data' => [
                'access_token'  => $token,
            ]
        ]);

    }
}
