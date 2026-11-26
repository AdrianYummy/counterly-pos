<?php

use App\Http\Middleware\EnforceSessionSecurity;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\NoStoreResponse;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('web', NoStoreResponse::class);
        $middleware->appendToGroup('web', SecurityHeaders::class);
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'session.secure' => EnforceSessionSecurity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
