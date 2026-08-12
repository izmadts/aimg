<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        // Some hosts (older MySQL/MariaDB, common on shared hosting) cap index
        // length below what utf8mb4 varchar(255) needs (255 * 4 = 1020 bytes).
        // Capping the default string length to 191 keeps indexed columns like
        // email under that limit (191 * 4 = 764 bytes) without touching every
        // migration individually.
        Schema::defaultStringLength(191);

        Gate::before(function ($user) {
            return $user->isAdmin() ? true : null;
        });

        // Every permission in the DB becomes a Gate the same slug can be checked
        // with via @can()/can() throughout the app. Guarded so a fresh install
        // (migrations not yet run) doesn't crash on boot.
        if (Schema::hasTable('permissions')) {
            foreach (Permission::all() as $permission) {
                Gate::define($permission->slug, function ($user) use ($permission) {
                    return $user->hasPermission($permission->slug);
                });
            }
        }
    }
}
