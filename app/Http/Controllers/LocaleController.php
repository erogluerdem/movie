<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LocaleController extends Controller
{
    /**
     * Switch application locale via POST request.
     */
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:'.implode(',', SetLocale::SUPPORTED_LOCALES)],
        ]);

        $locale = $validated['locale'];
        $request->session()->put('locale', $locale);
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if ($request->user()) {
            $request->user()->update(['preferred_language' => $locale]);
        }

        return back()->withCookie(cookie()->forever('locale', $locale));
    }

    /**
     * Switch application locale via GET request URL.
     */
    public function switchGet(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, SetLocale::SUPPORTED_LOCALES, true)) {
            $locale = 'tr';
        }

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);
        Carbon::setLocale($locale);

        if ($request->user()) {
            $request->user()->update(['preferred_language' => $locale]);
        }

        return back()->withCookie(cookie()->forever('locale', $locale));
    }
}
