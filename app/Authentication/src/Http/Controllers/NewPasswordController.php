<?php

namespace App\Authentication\Http\Controllers;

use Illuminate\Support\Str;
use App\Framework\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Authentication\Http\Requests\NewPasswordRequest;
use App\Authentication\Http\Responses\PasswordResetResponse;
use App\Authentication\Http\Responses\FailedPasswordResetResponse;

class NewPasswordController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(NewPasswordRequest $request)
    {
        $request->validated();

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->fill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
                    ? (new PasswordResetResponse($status))
                    : (new FailedPasswordResetResponse($status));
    }

}
