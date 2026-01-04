<?php

namespace App\Providers;

use App\Listeners\SendPasswordResetEmail;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        PasswordReset::class => [
            SendPasswordResetEmail::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
