<?php

namespace App\Platform\Providers;

use App\Platform\Interfaces\MemberRepositoryInterface;
use App\Platform\Interfaces\UserRepositoryInterface;
use App\Platform\Repositories\MemberRepository;
use App\Platform\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(MemberRepositoryInterface::class, MemberRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
