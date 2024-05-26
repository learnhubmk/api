<?php

namespace App\Website\Http\Controllers;

use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Endpoint;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Spatie\MailcoachSdk\Exceptions\InvalidData;
use App\Website\Http\Requests\StoreNewsletterSubscriberRequest;

class NewsletterSubscriberController extends Controller
{
    #[Endpoint('Subscribe To Newsletter', <<<'DESC'
  This endpoint is for subscribing to Leanhub.mk.
  Additionally it uses Cloudflare Turnstile ReCaptcha for validation.
 DESC)]
    #[Group('Website')]
    public function store(StoreNewsletterSubscriberRequest $request)
    {

        $firstName = $request->input('firstName');
        $email = $request->input('email');

        try {

            $subscriber = Mailcoach::createSubscriber(
                emailListUuid: config('mailcoach-sdk.email_list'),
                attributes: [
                'firstName' => $firstName,
                'email' => $email,
            ]
            );

        } catch (InvalidData $e) {

            return response()->json(["message" => "Веќе сте претплатени на нашиот билтен!"], 422);
        }

        return response()->json(["message" => "Успешно! Ви благодариме што се претплативте на нашиот билтен! Ве молиме потврдете ја вашата претпата на вашата електронска пошта."], 200);

    }
}
