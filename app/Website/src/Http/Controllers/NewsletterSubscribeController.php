<?php

namespace App\Website\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Knuckles\Scribe\Attributes\Endpoint;
use App\Website\Http\Requests\SubscribeRequest;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterSubscribeController extends Controller
{
    #[Endpoint('Website/Subscribe', <<<'DESC'
  This endpoint is for subscribing to Leanhub.mk.
  Additionally it uses Cloudflare Turnstile ReCaptcha for validation.
 DESC)]
    public function subscribe(SubscribeRequest $request) {

        $first_name = $request->input('first_name');
        $email = $request->input('email');

        $executed = RateLimiter::attempt(
            'subscribe:' . $email,
            $perMinute = 5,
            function() use ($first_name, $email) {

                $response = Http::post('https://mailcoach.learnhub.mk/subscribe/c7d93b24-50a3-4d64-bc95-f0e1b0d67b9a', [
                'first_name' => $first_name,
                'email' => $email,
                ]);
                dd($response);

                return response()->json(["message" => "Successful subcription"], 200);
            }
        );

        if (! $executed) {
            return response()->json(["message" => 'Too many subscription attempts.Try later!'], 429);
        }

    }
}
