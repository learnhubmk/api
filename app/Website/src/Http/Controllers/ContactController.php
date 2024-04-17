<?php

namespace App\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Website\Http\Requests\ContactFormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use App\Website\Mail\ContactMail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;

class ContactController extends Controller
{
    #[Endpoint(title: "Contact", description: "This endpoint is used for submitting contact form data.")]
    #[Group('Website')]
    #[BodyParam(name: "first_name", type: "string", description: "The first name of the user.", required: true)]
    #[BodyParam(name: "last_name", type: "string", description: "The last name of the user.", required: true)]
    #[BodyParam(name: "email", type: "string", description: "The email address of the user.", required: true)]
    #[BodyParam(name: "subject", type: "string", description: "The subject of the message.", required: true)]
    #[BodyParam(name: "message", type: "string", description: "The body of the message.", required: true)]
    #[BodyParam(name: "cf-turnstile-response", type: "string", description: "Cloudflare Turnstile ReCaptcha token")]
    public function __invoke(ContactFormRequest $request): JsonResponse
    {
        Mail::to(config('mail.contact_email'))->queue(new ContactMail($request->validated()));

        return response()->json(['message' => 'Your message has been sent successfully!'], Response::HTTP_OK);
    }

}
