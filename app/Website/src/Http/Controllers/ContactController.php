<?php

namespace App\Website\Http\Controllers;

use App\Framework\Http\Controllers\Controller;
use App\Website\Http\Requests\ContactFormRequest;
use App\Website\Mail\ContactEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;

class ContactController extends Controller
{
    #[Endpoint(title: "Contact", description: "This endpoint is used for submitting contact form data.")]
    #[Group('Website')]
    #[BodyParam(name: "name", type: "string", description: "The name of the visitor.", required: true)]
    #[BodyParam(name: "email", type: "string", description: "The email address of the visitor.", required: true)]
    #[BodyParam(name: "message", type: "string", description: "The body of the message.", required: true)]
    #[BodyParam(name: "cf-turnstile-response", type: "string", description: "Cloudflare Turnstile ReCaptcha token")]
    public function __invoke(ContactFormRequest $request): JsonResponse
    {
        Mail::to(config('mail.contact_email'))->queue(new ContactEmail($request->validated()));

        return response()->json(['message' => 'Вашата порака е успешно испратена!'], Response::HTTP_OK);
    }

}
