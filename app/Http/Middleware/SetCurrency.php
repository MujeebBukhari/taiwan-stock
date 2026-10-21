<?php

namespace App\Http\Middleware;

use App\Support\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
{
    /**
     * Handle an incoming request and ensure active display currency is initialized.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! empty($user->currency) && Currency::isSupported($user->currency)) {
            $currency = $user->currency;
            if ($request->hasSession() && $request->session()->get('currency') !== $currency) {
                $request->session()->put('currency', $currency);
            }
        } elseif ($request->hasSession() && $request->session()->has('currency')) {
            $currency = (string) $request->session()->get('currency');
            if (! Currency::isSupported($currency)) {
                $currency = Currency::default();
                $request->session()->put('currency', $currency);
            }
        } else {
            $currency = Currency::default();
        }

        View::share('activeCurrency', $currency);
        View::share('activeCurrencySymbol', Currency::symbol($currency));

        return $next($request);
    }
}
