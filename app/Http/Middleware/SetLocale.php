<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * Sets the application locale from the session if it has been set,
     * otherwise checks for a cookie fallback, then falls back to the
     * application default locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;
        $availableLocales = array_keys(config('app.available_locales', ['en' => 'English']));

        // Strategy 1: Check session (primary)
        try {
            if (Session::has('locale')) {
                $locale = Session::get('locale');
            }
        } catch (\Throwable $e) {
            // Session driver may fail (e.g., sessions table doesn't exist)
        }

        // Strategy 2: Check cookie fallback (set by LanguageController)
        if (!$locale && $request->hasCookie('locale')) {
            $locale = $request->cookie('locale');
            // Also restore to session so next requests use session
            try {
                Session::put('locale', $locale);
            } catch (\Throwable $e) {}
        }

        // Apply locale if valid
        if ($locale && in_array($locale, $availableLocales)) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
