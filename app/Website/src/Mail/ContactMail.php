<?php

namespace App\Website\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;
    public array $contactData;
    /**
     * Create a new message instance.
     */
    public function __construct(array $contactData)
    {
        $this->contactData = $contactData;
    }

    public function build(): ContactMail
    {
        return $this->subject('Contact Mail')
            ->view('website::mail.contact')
            ->with([
                'data' => $this->contactData
            ]);
    }
}
