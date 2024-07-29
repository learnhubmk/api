<?php

namespace App\Framework\Providers;

use App\Framework\Models\User;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider as LaravelTelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(LaravelTelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();

        Relation::morphMap([
            'user' => User::class,
        ]);

        View::addNamespace('website', app_path('Website/resources/views'));
        View::addNamespace('authentication', app_path('Authentication/resources/views'));
        View::addNamespace('platform', app_path('Platform/resources/views'));
        View::addNamespace('admin', app_path('Admin/resources/views'));

        RateLimiter::for('login', function (Request $request) {
            return [
                Limit::perMinute(5),
                Limit::perMinute(5)->by($request->input('email')),
            ];
        });
    }
}
