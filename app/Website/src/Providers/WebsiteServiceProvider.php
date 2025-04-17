<?php

namespace App\Website\Providers;

use App\Website\Service\MailboxLayerService;
use Illuminate\Support\ServiceProvider;

class WebsiteServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MailboxLayerService::class, function (): MailboxLayerService {
            return new MailboxLayerService();
        });
    }

    public function boot(): void
    {
    }
}
