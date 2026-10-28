@props(['fullWidth' => false, 'title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="h-full bg-slate-100/80">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('common.app_name').' — '.__('common.app_tagline') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-slate-100/80 text-slate-900 selection:bg-blue-600 selection:text-white">
        <!-- Guest Currency & Language Selector -->
        <div class="absolute top-4 right-4 z-30 flex items-center space-x-2">
            <div class="flex items-center text-xs font-semibold rounded-lg border border-slate-200 bg-white shadow-xs p-0.5" role="group" aria-label="Currency Selector">
                <a href="{{ route('currency.switch', 'TWD') }}"
                   id="guest-curr-switch-twd"
                   class="px-2.5 py-1 rounded-md transition-all duration-150 {{ \App\Support\Currency::getUserCurrency() === 'TWD' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                   title="Switch to TWD">
                    NT$ TWD
                </a>
                <span class="text-slate-300 px-0.5 text-[10px]">|</span>
                <a href="{{ route('currency.switch', 'USD') }}"
                   id="guest-curr-switch-usd"
                   class="px-2.5 py-1 rounded-md transition-all duration-150 {{ \App\Support\Currency::getUserCurrency() === 'USD' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                   title="Switch to USD">
                    US$ USD
                </a>
            </div>

            <div class="flex items-center text-xs font-semibold rounded-lg border border-slate-200 bg-white shadow-xs p-0.5" role="group" aria-label="Language Selector">
                <a href="{{ route('locale.switch', 'en') }}"
                   id="guest-lang-switch-en"
                   class="px-2.5 py-1 rounded-md transition-all duration-150 {{ app()->getLocale() === 'en' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                   title="Switch to English">
                    EN
                </a>
                <span class="text-slate-300 px-0.5 text-[10px]">|</span>
                <a href="{{ route('locale.switch', 'zh_TW') }}"
                   id="guest-lang-switch-zhtw"
                   class="px-2.5 py-1 rounded-md transition-all duration-150 {{ app()->getLocale() === 'zh_TW' ? 'bg-blue-50 text-blue-700 font-bold border border-blue-200/80 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                   title="切換至繁體中文">
                    繁中
                </a>
            </div>
        </div>

        @if ($fullWidth)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8 bg-slate-100/80 relative overflow-hidden">
                <!-- Branding Header -->
                <div class="mb-8 text-center relative z-10">
                    <a href="/" class="inline-flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-xl p-1 transition">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B1528] text-blue-400 border border-slate-800 font-bold shadow-xs group-hover:scale-105 transition-transform duration-150">
                            <x-application-logo class="w-6 h-6" />
                        </div>
                        <div class="text-left">
                            <span class="block text-xl font-bold tracking-tight text-slate-900 leading-tight">{{ __('common.app_name') }}</span>
                            <span class="text-xs text-slate-500 font-medium tracking-wide">{{ __('common.app_tagline') }}</span>
                        </div>
                    </a>
                </div>

                <!-- Centered Card -->
                <div class="w-full sm:max-w-md p-8 bg-white border border-slate-200/90 shadow-card rounded-xl text-slate-900 relative z-10">
                    {{ $slot }}
                </div>
            </div>
        @endif
    </body>
</html>
