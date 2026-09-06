<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Services\ErrorLogService;

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
            'permission.team' => \App\Http\Middleware\SetPermissionsTeamId::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'billing/payment/*',
            'registration/payment/*',
            'iclock/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e) {
            ErrorLogService::log($e, ['panel' => 'global']);
        });
    })->create();