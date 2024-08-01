<?php

namespace App\Authentication\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\BodyParam;
use App\Authentication\Http\Requests\PasswordResetLinkRequest;

class PasswordResetLinkController extends Controller
{
    #[Endpoint(title: 'Password Reset Link', description: 'This endpoint enables to send reset link for forgotten password')]
    #[Group('Authentication')]
    #[BodyParam('email', required: true)]
    public function __invoke(PasswordResetLinkRequest $request)
    {
        $request->validated();

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? response()->json(['message' => __($status)], 200)
                    : response()->json(['message' => __($status)], 404);

    }

}
