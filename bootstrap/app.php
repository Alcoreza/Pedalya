<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\EnsureUserHasRole;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureUserHasRole::class]);
        $middleware->validateCsrfTokens(except: ['webhooks/maya']);

        // ngrok terminates HTTPS and forwards requests to the local HTTP server.
        // Trust forwarded headers in local development so generated asset and
        // callback URLs use the public HTTPS scheme.
        if (env('APP_ENV', 'production') === 'local') {
            $middleware->trustProxies(at: '*');
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
