<?php

namespace App\Framework\Listeners;

use App\Framework\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Mail::to($event->user->email)->send(new WelcomeEmail($event->user));
    }
}
