<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported application locales.
     *
     * @var list<string>
     */
    public const SUPPORTED_LOCALES = ['zh_TW', 'en'];

    public const DEFAULT_LOCALE = 'zh_TW';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $defaultLocale = config('app.locale', self::DEFAULT_LOCALE);
        if (! in_array($defaultLocale, self::SUPPORTED_LOCALES, true)) {
            $defaultLocale = self::DEFAULT_LOCALE;
        }

        $locale = $request->hasSession()
            ? $request->session()->get('locale', $defaultLocale)
            : $defaultLocale;

        if (! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = $defaultLocale;
        }

        App::setLocale($locale);

        Carbon::setLocale($locale === 'zh_TW' ? 'zh-TW' : 'en');

        return $next($request);
    }
}
