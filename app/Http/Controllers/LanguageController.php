<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
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
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        return redirect()->back();
    }
}
