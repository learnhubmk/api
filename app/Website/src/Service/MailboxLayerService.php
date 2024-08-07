<?php

namespace App\Website\Service;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class MailboxLayerService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = Config::get('mailboxlayer.base_url');
    }

    /**
     * @throws Exception
     */
    public function check($email)
    {
        $accessKey = Config::get('mailboxlayer.access_key');
        $response = Http::get("{$this->baseUrl}/bulk_check", [
            'access_key' => $accessKey,
            'email' => $email,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to fetch data from MailboxLayer API');
        }

        return $response->json();
    }
}
