<?php

namespace App\Platform\Http\Controllers;

use App\Framework\Enums\RoleName;
use App\Framework\Models\User;
use App\Platform\Http\Requests\RegisterRequest;
use App\Platform\Http\Resources\MemberProfileResource;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

class RegisterMemberController
{
    #[Group('Platform')]
    #[Endpoint(title: 'Sign Up', description: 'This endpoint is for registering new members')]
    #[BodyParam('email', 'string', required: true, example: 'example@test.com')]
    #[BodyParam('password', 'string', required: true, example: 'password')]
    #[BodyParam('first_name', 'string', required: true, example: 'John')]
    #[BodyParam('last_name', 'string', required: true, example: 'Doe')]
    #[BodyParam('image', 'file', required: false)]
    public function store(RegisterRequest $request): MemberProfileResource
    {
        return DB::transaction(function () use ($request) {
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
                    'access_token' => $token,
                ],
            ]);
        });
    }
}
