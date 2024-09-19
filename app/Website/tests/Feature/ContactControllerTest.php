<?php

namespace App\Website\Tests\Feature;

use App\Website\Mail\ContactEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test */
    public function contact_form_submission_succeeds(): void
    {
        Mail::fake();

        $formData = [
            'name' => 'Malista',
            'email' => 'malista.polikala@onnet.mk',
            'message' => 'Test message content',
        ];

        $response = $this->postJson(route('contact'), $formData);

        $response->assertOk()
            ->assertJson(['message' => 'Вашата порака е успешно испратена!']);

        Mail::assertQueued(ContactEmail::class, function ($mail) use ($formData) {
            return $mail->hasTo(config('mail.contact_email')) &&
                $mail->contactData['name'] === $formData['name'] &&
                $mail->contactData['email'] === $formData['email'] &&
                $mail->contactData['message'] === $formData['message'];
        });
    }

    /** @test */
    public function contact_form_submission_fails_on_mail_error(): void
    {
        Mail::fake();

        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Mail sending failed'));

        $formData = [
            'name' => 'Malista',
            'email' => 'malista.polikala@on.net.mk',
            'message' => 'Test failure message content',
        ];

        $response = $this->postJson(route('contact'), $formData);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Mail sending failed']);
    }
}
