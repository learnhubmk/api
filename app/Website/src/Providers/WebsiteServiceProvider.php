<?php

namespace App\Website\Providers;

use App\Website\Service\MailboxLayerService;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class WebsiteServiceProvider extends ServiceProvider
{
    public function register()
    {

        $this->app->singleton('mailboxlayer', function () {
            return new MailboxLayerService(new Client);
        });
    }

    public function boot() {}
}
