<?php

namespace App\Providers;

use App\Broadcasting\SmsChannel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Notification;
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
        Notification::extend('sms', function ($app) {
            return new SmsChannel();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MariaDB compatibility
        Schema::defaultStringLength(191);

        // Set umask to 0002, so that the files created by the web server are writable by the web server
        umask(0002);

        if (env('FORCE_HTTPS', true)) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        Paginator::useBootstrap();
    }
}
