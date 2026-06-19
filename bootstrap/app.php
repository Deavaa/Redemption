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
        //
        // LOOP BUG FIX (was: user could not log back in after session expiry):
        //
        // The previous implementation redirected POST /login → GET /login on
        // CSRF mismatch. That redirect is itself a navigation, and on a flaky
        // network (or with a cached page, or with a session that was GC'd
        // between the GET and POST) the user could end up submitting the form
        // with ANOTHER stale token, which would trigger ANOTHER redirect to
        // /login — an infinite "Your session expired" loop.
        //
        // New behavior:
        //   • For the login POST specifically, we do NOT redirect. We:
        //       1. Force-invalidate the stale session and regenerate a new ID.
        //       2. Render the auth.login view directly with a brand-new CSRF
        //          token and a one-shot "Your session expired, please try
        //          again" message. The user immediately sees a working form
        //          and their next submit is guaranteed to have a valid token.
        //   • For all other routes, we keep the redirect-to-login behavior
        //     (with url.intended preserved) since those aren't susceptible to
        //     the same loop.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // For AJAX/JSON requests, return a JSON error
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'error' => 'CSRF token expired. Please refresh the page.',
                    'csrf_expired' => true,
                ], 419);
            }

            // ── Login POST: render login view directly (NO redirect) ──
            // This breaks the previous infinite-loop bug where the redirect
            // itself triggered another CSRF mismatch.
            if ($request->is('login') || $request->is('/login')) {
                try {
                    // Destroy the stale session and start a clean one so the
                    // form below gets a brand-new _token that will match on
                    // the next POST.
                    $request->session()->invalidate();
                    $request->session()->regenerate();
                    $request->session()->regenerateToken();
                    $request->session()->save();
                } catch (\Throwable $sessionError) {
                    // Last resort — keep going, the view will still render.
                }

                return response()
                    ->view('auth.login', [
                        // Preserve the login identifier so the user doesn't
                        // have to retype their email/ID.
                        'login' => $request->input('login', ''),
                    ])
                    ->with('error', 'Your previous session expired. Please sign in again.')
                    ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
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
