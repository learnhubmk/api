<?php

namespace App\Platform\Http\Controllers\Auth;

use App\Platform\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends BaseApiController
{
    /**
     * Send a new email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if ($request->member()->hasVerifiedEmail()) {
            return $this->response([], [], 400, 'Email already verified');
        }

        $request->member()->sendEmailVerificationNotification();

        return $this->response([], [], 400, 'Verification link send');
    }
}
