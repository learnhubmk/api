<?php

namespace App\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Website\Http\Requests\ContactFormRequest;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use App\Website\Mail\ContactMail;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller
{
    #[Endpoint(title: "Submit Contact Form", description: "This endpoint is used for submitting contact form data.")]
    #[BodyParam(name: "first_name", type: "string", description: "The first name of the user.", required: true)]
    #[BodyParam(name: "last_name", type: "string", description: "The last name of the user.", required: true)]
    #[BodyParam(name: "email", type: "string", description: "The email address of the user.", required: true)]
    #[BodyParam(name: "subject", type: "string", description: "The subject of the message.", required: true)]
    #[BodyParam(name: "message", type: "string", description: "The body of the message.", required: true)]
    public function __invoke(ContactFormRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $mailData = $request->validated();
            $contactAddress = config('mail.contact_email');

            Mail::to($contactAddress)->send(new ContactMail($mailData));
            return response()->json(['message' => 'Your message has been sent successfully!'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send your message, please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

}
