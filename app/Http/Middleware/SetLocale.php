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
     * otherwise falls back to the application default locale.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale');
            $availableLocales = array_keys(config('app.available_locales', ['en' => 'English']));

            if (in_array($locale, $availableLocales)) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}
