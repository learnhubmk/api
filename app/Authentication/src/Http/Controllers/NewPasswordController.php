<?php

namespace App\Authentication\Http\Controllers;

use App\Framework\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Authentication\Http\Requests\NewPasswordRequest;

class NewPasswordController extends Controller
{
    #[Endpoint(title: 'New password', description: 'This endpoint enables to set new password.[IMPORTANT] Please add the password_confirmation also!!')]
    #[Group('Authentication')]
    #[BodyParam('email', 'password', 'token',  required: true)]
    public function __invoke(NewPasswordRequest $request)
    {
        $request->validated();

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->fill([
                    'password' => Hash::make($password)
                ]);

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 404);
    }

}
