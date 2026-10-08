<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
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
        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        // Ensure public/storage symlink exists on containerized / cloud environments
        if (! file_exists(public_path('storage')) && is_dir(storage_path('app/public'))) {
            @symlink(storage_path('app/public'), public_path('storage'));
        }
    }
}
