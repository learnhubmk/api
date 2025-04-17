<?php

namespace App\Authentication\Http\Controllers;

use App\Authentication\Http\Requests\NewPasswordRequest;
use App\Framework\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

class NewPasswordController extends Controller
{
    #[Endpoint(title: 'New password', description: 'This endpoint enables to set new password.[IMPORTANT] Please add the password_confirmation also!!')]
    #[Group('Authentication')]
    #[BodyParam('email', 'password', 'token', required: true)]
    public function __invoke(NewPasswordRequest $request): JsonResponse
    {

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->update([
                    'password' => Hash::make($password),
                ]);

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 404);
    }
}
