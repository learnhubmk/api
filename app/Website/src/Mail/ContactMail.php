<?php

namespace App\Website\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function build(): ContactMail
    {
        return $this->subject('Contact Mail')
            ->markdown('website::mail.contact', [
                'data' => $this->contactData
            ]);
    }
}
