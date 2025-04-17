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
        $this->baseUrl = is_string(Config::get('mailboxlayer.base_url'))
            ? Config::get('mailboxlayer.base_url')
            : 'https://apilayer.net/api';
    }

    /**
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function check(string $email): array
    {
        $accessKey = is_string(Config::get('mailboxlayer.access_key'))
            ? Config::get('mailboxlayer.access_key')
            : throw new Exception('MailboxLayer access key is not set.');

        $response = Http::get("{$this->baseUrl}/bulk_check", [
            'access_key' => $accessKey,
            'email' => $email,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to fetch data from MailboxLayer API');
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new Exception('Invalid response from MailboxLayer API');
        }

        return $data;
    }
}
