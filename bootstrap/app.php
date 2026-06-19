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
            // SECURITY: 'admin/session-diagnostic' was previously CSRF-excluded.
            // It exposes session IDs, handler class, and DB row counts. It is
            // still routed but now requires a valid CSRF token like all other
            // admin POST routes. If you need to test it via curl, fetch the
            // CSRF token from the page first.
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
        // ── Handle CSRF token mismatches (419 Page Expired) ──
        // Instead of showing a generic "Page Expired" error, redirect back
        // to the login page with a user-friendly message. This fixes the
        // "session expired after re-login" bug where the user gets stuck
        // in a loop after their session expires.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // For AJAX/JSON requests, return a JSON error
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'error' => 'CSRF token expired. Please refresh the page.',
                    'csrf_expired' => true,
                ], 419);
            }

            // For the login POST specifically, redirect back to login with a message
            // (instead of showing the 419 error page)
            if ($request->is('login') || $request->is('/login')) {
                return redirect()->route('login')
                    ->with('error', 'Your session expired. Please try logging in again.')
                    ->withInput($request->except('password', '_token'));
            }

            // For all other pages, redirect to login with the intended URL preserved
            // Use path() instead of getRequestUri() to avoid the double-path bug
            // (getRequestUri() includes the subdirectory prefix which gets doubled by Laravel's URL generator)
            $intended = '/' . $request->path();
            // Don't store login URL as intended
            if ($intended !== '/login' && !str_ends_with($intended, '/login')) {
                session(['url.intended' => $intended]);
            }

            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please log in again to continue.');
        });
    })->create();
