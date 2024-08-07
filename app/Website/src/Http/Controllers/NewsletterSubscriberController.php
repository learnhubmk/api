<?php

namespace App\Website\Http\Controllers;

use App\Framework\Http\Controllers\Controller;
use App\Website\Http\Requests\StoreNewsletterSubscriberRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Spatie\MailcoachSdk\Exceptions\InvalidData;
use Spatie\MailcoachSdk\Facades\Mailcoach;
use Symfony\Component\HttpFoundation\Response;

class NewsletterSubscriberController extends Controller
{
    #[Endpoint('Subscribe To Newsletter', <<<'DESC'
  This endpoint is for subscribing to the Leanhub.mk newsletter.
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

            return response()->json(['message' => 'Електронската пошта е веќе претплатена!'], 422);
        }

        return response()->json(['message' => 'Успешно! Ве молиме потврдете ја вашата претпата на вашата електронска пошта.'], Response::HTTP_OK);

    }
}
