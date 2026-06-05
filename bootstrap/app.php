<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureIsAdmin::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
        ]);

        // SEC-10: Add security headers to all web responses
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\TrackVisitorSession::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/facebook',
            'webhook/pathao',
            'track-event',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
