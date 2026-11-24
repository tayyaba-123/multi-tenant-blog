<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Writers sign in on their tenant's domain, admins on the central domain.
        $middleware->redirectGuestsTo(fn () => tenancy()->initialized
            ? route('login')
            : route('central.login'));

        $middleware->redirectUsersTo(fn () => tenancy()->initialized
            ? route('dashboard')
            : route('central.tenants.index'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
