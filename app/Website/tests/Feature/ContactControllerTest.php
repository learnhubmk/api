<?php

namespace App\Website\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Mail;
use App\Website\Mail\ContactMail;

class ContactControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

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

        $response = $this->postJson(route('contact'), $formData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Your message has been sent successfully!']);

        Mail::assertQueued(ContactMail::class, function ($mail) use ($formData) {
            return $mail->hasTo(config('mail.contact_email')) &&
                $mail->contactData['first_name'] === $formData['first_name'] &&
                $mail->contactData['last_name'] === $formData['last_name'] &&
                $mail->contactData['email'] === $formData['email'] &&
                $mail->contactData['subject'] === $formData['subject'] &&
                $mail->contactData['message'] === $formData['message'];
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

        $response = $this->postJson(route('contact'), $formData);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Mail sending failed']);
    }
}
