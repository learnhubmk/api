<?php

namespace App\Platform\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;

class EmailVerificationController
{
    #[Group('Platform')]
    #[Endpoint(title: 'Verify E-mail Address', description: 'This endpoint is validating the e-mail addresses')]
    #[UrlParam('id', type: 'integer', description: 'The ID of the user.', example: 1)]
    #[UrlParam('hash', type: 'string', description: 'The email verification hash.', example: '123456abcdef')]
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return response()->json([
            'message' => __('Email verified successfully'),
            'status' => 200
        ]);
    }
}
