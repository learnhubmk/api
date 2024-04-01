<?php

namespace App\Website\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Mail;
use App\Website\Mail\ContactMail;

class ContactFormControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function contact_form_submission_succeeds()
    {
        $formData = [
            'first_name' => 'Malista',
            'last_name' => 'Polikala',
            'email' => 'malista.polikala@on.net.mk',
            'subject' => 'Test Subject',
            'message' => 'Test message content'
        ];

        $response = $this->postJson('/api/website/contact', $formData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Your message has been sent successfully!']);

        Mail::assertSent(ContactMail::class, function ($mail) use ($formData) {
            return $mail->hasTo(config('mail.contact_email')) &&
                $mail->contactData['first_name'] === 'Malista';
                $mail->contactData['last_name'] === 'Polikala';
                $mail->contactData['email'] === 'malista.polikala@on.net.mk';
                $mail->contactData['subject'] === 'Test Subject';
                $mail->contactData['message'] === 'Test message content';
        });
    }

    /** @test */
    public function contact_form_submission_fails_on_mail_error()
    {
        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Mail sending failed'));

        $formData = [
            'first_name' => 'Malista',
            'last_name' => 'Polikala',
            'email' => 'malista.polikala@on.net.mk',
            'subject' => 'Test Failure Subject',
            'message' => 'Test failure message content'
        ];

        $response = $this->postJson('/api/website/contact', $formData);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Failed to send your message, please try again later.']);
    }
}
