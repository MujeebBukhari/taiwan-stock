<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LocaleController extends Controller
{
    /**
     * Switch application language and redirect back to the previous page.
     */
    public function switch(Request $request, ?string $locale = null): RedirectResponse
    {
        $selected = $locale ?: $request->input('locale');

        if (in_array($selected, SetLocale::SUPPORTED_LOCALES, true)) {
            $request->session()->put('locale', $selected);

            if ($request->user() && Schema::hasColumn('users', 'locale')) {
                $request->user()->update(['locale' => $selected]);
            }
        }

        $fallback = auth()->check() ? route('dashboard') : route('login');
        $previous = url()->previous();

        if (empty($previous) || str_contains($previous, '/locale/')) {
            return redirect()->to($fallback);
        }

        return redirect()->back(fallback: $fallback);
    }
}
