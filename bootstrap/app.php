<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'setup.wizard'   => \App\Http\Middleware\RedirectToSetupWizard::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'billing.check' => \App\Http\Middleware\CheckBillingStatus::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'billing/payment/*',
            'registration/payment/*',
            'iclock/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();