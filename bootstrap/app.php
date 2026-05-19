<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\BranchScope;
use App\Http\Middleware\ParentMiddleware;
use App\Http\Middleware\StudentMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (needed for XAMPP HTTPS / reverse proxies)
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'telegram/webhook',
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'parent' => ParentMiddleware::class,
            'student' => StudentMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'branch-scope' => BranchScope::class,
            'locale' => SetLocale::class,
        ]);
        // Apply SetLocale to all web routes
        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
