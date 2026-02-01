<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Policies\FolderPolicy;
use App\Policies\DocumentPolicy;
use App\Models\Folder;
use App\Models\Document;

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

        // Register policies
        \Illuminate\Support\Facades\Gate::policy(Folder::class, FolderPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(Document::class, DocumentPolicy::class);
    }
}
