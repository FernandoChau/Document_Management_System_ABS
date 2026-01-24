<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register route middleware alias for check authentication status
        $router = $this->app['router'];
        $router->aliasMiddleware('check.auth.status', \App\Http\Middleware\CheckAuthenticationStatus::class);
    }
}
