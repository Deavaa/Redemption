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
use App\Http\Middleware\SessionKeepAlive;
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
            'admin/keepalive',  // Keepalive must work even with stale CSRF token
        ]);
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'parent' => ParentMiddleware::class,
            'student' => StudentMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'branch-scope' => BranchScope::class,
            'locale' => SetLocale::class,
        ]);
        // Apply SessionKeepAlive FIRST (before other middleware) to override PHP's
        // native session garbage collection that kills sessions in <5 min on XAMPP.
        // Then apply SetLocale to all web routes.
        $middleware->web(prepend: [
            SessionKeepAlive::class,
        ]);
        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
