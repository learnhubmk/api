<?php

namespace App\Website\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Knuckles\Scribe\Attributes\Endpoint;

class NewsletterSubscribeController extends Controller
{
       #[Endpoint('Website/Subscribe', <<<'DESC'
  This endpoint is for subscribing to Leanhub.mk.
  Additionally it uses Cloudflare Turnstile ReCaptcha for validation.
 DESC)]
    public function subscribe(Request $request) {

    }
}
