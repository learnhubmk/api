<?php

return [
    Bugsnag\BugsnagLaravel\BugsnagServiceProvider::class,
    App\Admin\Providers\AdminServiceProvider::class,
    App\Authentication\Providers\AuthenticationServiceProvider::class,
    App\Content\Providers\ContentServiceProvider::class,
    App\Framework\Providers\AppServiceProvider::class,
    App\Framework\Providers\AuthServiceProvider::class,
    App\Framework\Providers\BroadcastServiceProvider::class,
    App\Framework\Providers\EventServiceProvider::class,
    App\Framework\Providers\HorizonServiceProvider::class,
    App\Framework\Providers\RouteServiceProvider::class,
    //App\Framework\Providers\TelescopeServiceProvider::class,
    App\Platform\Providers\PlatformServiceProvider::class,
    App\Website\Providers\WebsiteServiceProvider::class,
];
