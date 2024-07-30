<?php

namespace App\Authentication\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Password;
use App\Authentication\Http\Requests\PasswordResetLinkRequest;
use App\Authentication\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use App\Authentication\Http\Responses\FailedPasswordResetLinkRequestResponse;

class PasswordResetLinkController extends Controller
{
    public function __invoke(PasswordResetLinkRequest $request)
    {
        $request->validated();

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? (new SuccessfulPasswordResetLinkRequestResponse($status))
                    : (new FailedPasswordResetLinkRequestResponse($status));

    }

}
