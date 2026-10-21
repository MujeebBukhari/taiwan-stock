<?php

namespace App\Http\Controllers;

use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CurrencyController extends Controller
{
    /**
     * Switch application display currency and redirect back to the previous page.
     */
    public function switch(Request $request, ?string $currency = null): RedirectResponse
    {
        $fallback = auth()->check() ? route('dashboard') : route('login');

        if ($request->isMethod('post')) {
            $rawCurrency = (string) ($currency ?: $request->input('currency'));
            $selected = strtoupper(trim($rawCurrency));

            if (! Currency::isSupported($selected)) {
                return redirect()->back(fallback: $fallback)
                    ->withErrors(['currency' => 'The selected currency is invalid or unsupported.']);
            }
        } else {
            $selected = strtoupper(trim((string) ($currency ?: $request->input('currency'))));
        }

        if (Currency::isSupported($selected)) {
            $request->session()->put('currency', $selected);

            if ($request->user() && Schema::hasColumn('users', 'currency')) {
                $request->user()->update(['currency' => $selected]);
            }
        }

        $previous = url()->previous();

        if (empty($previous) || str_contains($previous, '/currency')) {
            return redirect()->to($fallback);
        }

        return redirect()->back(fallback: $fallback);
    }
}
