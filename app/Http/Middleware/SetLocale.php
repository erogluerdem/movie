<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     *
     * @var array<string>
     */
    public const SUPPORTED_LOCALES = ['tr', 'en'];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionLocale = $request->session()->get('locale');
        $cookieLocale = $request->cookie('locale');

        // If cookie is provided and valid, prioritize or sync with it
        if ($cookieLocale && in_array($cookieLocale, self::SUPPORTED_LOCALES, true)) {
            $locale = $cookieLocale;
            if ($sessionLocale !== $locale) {
                $request->session()->put('locale', $locale);
            }
        } elseif ($sessionLocale && in_array($sessionLocale, self::SUPPORTED_LOCALES, true)) {
            $locale = $sessionLocale;
        } elseif ($request->user() && in_array($request->user()->preferred_language, self::SUPPORTED_LOCALES, true)) {
            $locale = $request->user()->preferred_language;
            $request->session()->put('locale', $locale);
        } else {
            $locale = (string) config('app.locale', 'tr');
            if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
                $locale = 'tr';
            }
            $request->session()->put('locale', $locale);
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
