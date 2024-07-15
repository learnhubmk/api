<?php

namespace App\Website\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Mail;
use App\Website\Mail\ContactEmail;

class ContactControllerTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    /** @test */
    public function contact_form_submission_succeeds()
    {
        Mail::fake();

        $formData = [
            'name' => 'Malista',
            'email' => 'malista.polikala@onnet.mk',
            'message' => 'Test message content'
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
    public function contact_form_submission_fails_on_mail_error()
    {
        Mail::fake();

        Mail::shouldReceive('to')
            ->andThrow(new \Exception('Пораката е неуспешно пратена!'));

        $formData = [
            'name' => 'Malista',
            'email' => 'malista.polikala@on.net.mk',
            'message' => 'Test failure message content'
        ];

        $response = $this->postJson(route('contact'), $formData);

        $response->assertStatus(500)
            ->assertJson(['message' => 'Пораката е неуспешно пратена!']);
    }
}
