<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', App\Http\Middleware\CheckUserIsActive::class);
        $middleware->appendToGroup('api', App\Http\Middleware\CheckUserIsActive::class);
        $middleware->alias([
            'restrict.ip' => App\Http\Middleware\RestrictIp::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->withSingletons([
        Illuminate\Contracts\Debug\ExceptionHandler::class => App\Exceptions\Handler::class,
    ])->create();
