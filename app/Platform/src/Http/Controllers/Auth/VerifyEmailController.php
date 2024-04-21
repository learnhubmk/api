<?php

namespace App\Platform\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Platform\Http\Controllers\BaseApiController;
use App\Platform\Models\Member;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class VerifyEmailController extends BaseApiController
{
    /**
     * Mark the authenticated member's email address as verified.
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(EmailVerificationRequest $request)
    {
        $member = Member::find($request->route('id'));

        if ($member->hasVerifiedEmail()) {
            return $this->response([], [], 404, 'Email is already verified');
        }

        if ($member->markEmailAsVerified()) {
            event(new Verified($member));
        }

        return $this->response([], [], 200, 'Success');
    }
}
