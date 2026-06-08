<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Switch the application locale.
     *
     * Validates the requested locale against available locales,
     * stores it in the session, and redirects back to the previous page.
     */
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $availableLocales = array_keys(config('app.available_locales', ['en' => 'English']));

        if (in_array($locale, $availableLocales)) {
            // Set locale immediately for this request
            App::setLocale($locale);

            // Try to persist in session (may fail if sessions table doesn't exist)
            try {
                Session::put('locale', $locale);
                Session::save();
            } catch (\Throwable $e) {
                // If database session fails, try cookie-based fallback
                \Log::warning('Session write failed in LanguageController: ' . $e->getMessage());
            }

            // Also set a cookie as a fallback (lasts 30 days)
            $cookie = cookie('locale', $locale, 43200);
            return redirect()->back()->withCookie($cookie);
        }

        return redirect()->back();
    }
}
