<?php

namespace App\Website\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactEmail extends Mailable
{
    use Queueable;
    use SerializesModels;
    /**
     * Create a new message instance.
     */
    public function __construct(public array $contactData)
    {
    }

    public function build(): ContactEmail
    {
        return $this->subject('LearnHub.mk: нова порака од контакт формата!')
            ->markdown('website::mail.contact', [
                'data' => $this->contactData
            ]);
    }
}
