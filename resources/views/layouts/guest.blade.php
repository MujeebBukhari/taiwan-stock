<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="h-full bg-slate-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? __('common.app_name').' — '.__('common.app_tagline') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-full font-sans antialiased bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-white">
        <!-- Guest Currency & Language Selector -->
        <div class="absolute top-4 right-4 z-30 flex items-center space-x-2">
            <div class="flex items-center text-xs font-semibold rounded-lg border border-slate-800 bg-slate-900/90 shadow-md p-0.5" role="group" aria-label="Currency Selector">
                <a href="{{ route('currency.switch', 'TWD') }}"
                   id="guest-curr-switch-twd"
                   class="px-2.5 py-1 rounded-md transition-colors {{ \App\Support\Currency::getUserCurrency() === 'TWD' ? 'bg-slate-800 text-emerald-400 font-bold shadow-xs' : 'text-slate-400 hover:text-white' }}"
                   title="Switch to TWD">
                    NT$ TWD
                </a>
                <span class="text-slate-700 px-0.5 text-[10px]">|</span>
                <a href="{{ route('currency.switch', 'USD') }}"
                   id="guest-curr-switch-usd"
                   class="px-2.5 py-1 rounded-md transition-colors {{ \App\Support\Currency::getUserCurrency() === 'USD' ? 'bg-slate-800 text-emerald-400 font-bold shadow-xs' : 'text-slate-400 hover:text-white' }}"
                   title="Switch to USD">
                    US$ USD
                </a>
            </div>

            <div class="flex items-center text-xs font-semibold rounded-lg border border-slate-800 bg-slate-900/90 shadow-md p-0.5" role="group" aria-label="Language Selector">
                <a href="{{ route('locale.switch', 'en') }}"
                   id="guest-lang-switch-en"
                   class="px-2.5 py-1 rounded-md transition-colors {{ app()->getLocale() === 'en' ? 'bg-slate-800 text-emerald-400 font-bold shadow-xs' : 'text-slate-400 hover:text-white' }}"
                   title="Switch to English">
                    EN
                </a>
                <span class="text-slate-700 px-0.5 text-[10px]">|</span>
                <a href="{{ route('locale.switch', 'zh_TW') }}"
                   id="guest-lang-switch-zhtw"
                   class="px-2.5 py-1 rounded-md transition-colors {{ app()->getLocale() === 'zh_TW' ? 'bg-slate-800 text-emerald-400 font-bold shadow-xs' : 'text-slate-400 hover:text-white' }}"
                   title="切換至繁體中文">
                    繁中
                </a>
            </div>
        </div>

        @if ($fullWidth)
            {{ $slot }}
        @else
            <div class="min-h-screen flex flex-col justify-center items-center px-4 py-12 sm:px-6 lg:px-8 bg-slate-950 relative overflow-hidden">
                <!-- Background Ambient Glow -->
                <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Branding Header -->
                <div class="mb-8 text-center relative z-10">
                    <a href="/" class="inline-flex items-center gap-3 group focus:outline-none focus:ring-2 focus:ring-emerald-500 rounded-xl p-1 transition">
                        <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold group-hover:bg-emerald-500/20 group-hover:border-emerald-500/40 transition-all duration-200">
                            <x-application-logo class="w-6 h-6" />
                        </div>
                        <div class="text-left">
                            <span class="block text-xl font-bold tracking-tight text-white leading-tight">{{ __('common.app_name') }}</span>
                            <span class="text-xs text-slate-400 font-medium tracking-wide">{{ __('common.app_tagline') }}</span>
                        </div>
                    </a>
                </div>

                <!-- Centered Card -->
                <div class="w-full sm:max-w-md p-8 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-2xl text-slate-900 dark:text-slate-100 relative z-10">
                    {{ $slot }}
                </div>
            </div>
        @endif
    </body>
</html>
