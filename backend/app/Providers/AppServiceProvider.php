<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Policies\FolderPolicy;
use App\Policies\DocumentPolicy;
use App\Models\Folder;
use App\Models\Document;
use App\Models\Department;
use App\Observers\FolderObserver;
use App\Observers\DepartmentObserver;

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

        // Register observers for slug synchronization
        Folder::observe(FolderObserver::class);
        Department::observe(DepartmentObserver::class);

        // Register polymorphic relations mapping for AuditLog
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            'Document' => \App\Models\Document::class,
            'Folder' => \App\Models\Folder::class,
            'Department' => \App\Models\Department::class,
            'Group' => \App\Models\Group::class,
            'DocumentPermission' => \App\Models\DocumentPermission::class,
            'FolderPermission' => \App\Models\FolderPermission::class,
            'DocumentContent' => \App\Models\DocumentContent::class,
            'User' => \App\Models\User::class,
            'ShareLink' => \App\Models\ShareLink::class,
            'Album' => \App\Models\Album::class,
            'Photo' => \App\Models\Photo::class,
        ]);
    }
}
