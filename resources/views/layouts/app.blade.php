<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Stock Portfolio') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased bg-slate-100/70 text-slate-900 selection:bg-blue-600 selection:text-white"
      x-data="{ sidebarOpen: false }"
      @keydown.escape.window="sidebarOpen = false">
    <div class="flex h-screen overflow-hidden">

        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs lg:hidden"
             @click="sidebarOpen = false"
             aria-hidden="true"></div>

        <!-- Mobile Sidebar Off-Canvas Drawer -->
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition ease-in-out duration-200 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-150 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 flex flex-col w-72 bg-[#0B1528] text-slate-300 border-r border-slate-800/80 shadow-2xl lg:hidden select-none">

            <!-- Mobile App Branding Header -->
            <div class="flex items-center justify-between px-6 h-16 border-b border-slate-800/80 bg-[#070D19]/60 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600/15 text-blue-400 border border-blue-500/20 font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="font-bold text-white tracking-tight leading-none text-base">{{ __('common.app_name') }}</h1>
                        <span class="text-[11px] text-slate-400 tracking-wide">{{ __('common.app_tagline') }}</span>
                    </div>
                </div>
                <button type="button"
                        @click="sidebarOpen = false"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Close sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto fintech-scrollbar">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>{{ __('navigation.dashboard') }}</span>
                </a>

                <!-- Stocks Submenu -->
                <div class="space-y-1">
                    <div class="flex items-center gap-3 px-3 pt-3 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>{{ __('navigation.stocks') }}</span>
                    </div>
                    <div class="ml-4 pl-2 border-l border-slate-800 space-y-1">
                        <a href="{{ route('companies.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('companies.*') || request()->routeIs('admin.companies.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('companies.*') || request()->routeIs('admin.companies.*') ? 'bg-white' : 'bg-slate-500' }}"></span>
                            <span>{{ __('navigation.companies') }}</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('transactions.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('transactions.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>{{ __('navigation.transactions') }}</span>
                </a>

                <a href="{{ route('portfolio.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('portfolio.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    <span>{{ __('navigation.portfolio') }}</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('navigation.reports') }}</span>
                </a>

                <a href="{{ route('analytics.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('analytics.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>{{ __('navigation.analytics') }}</span>
                </a>

                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('settings.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('navigation.settings') }}</span>
                </a>
            </nav>

            <!-- Mobile User Info & Bottom Currency Indicator -->
            <div class="p-4 border-t border-slate-800/80 bg-[#070D19]/70 space-y-3 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'bg-purple-950/80 text-purple-300 border border-purple-800' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                        {{ auth()->user()->role ?? 'USER' }}
                    </span>
                </div>
                <div class="pt-2 flex items-center justify-between border-t border-slate-800 text-[11px]">
                    <span class="text-slate-400">{{ __('common.currency') }}:</span>
                    <div class="flex items-center gap-1.5 font-mono font-semibold">
                        <a href="{{ route('currency.switch', 'TWD') }}" class="{{ \App\Support\Currency::getUserCurrency() === 'TWD' ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">NT$ TWD</a>
                        <span class="text-slate-600">|</span>
                        <a href="{{ route('currency.switch', 'USD') }}" class="{{ \App\Support\Currency::getUserCurrency() === 'USD' ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">US$ USD</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Persistent Sidebar -->
        <aside class="hidden lg:flex flex-col w-64 bg-[#0B1528] text-slate-300 border-r border-slate-800/80 shrink-0 select-none">
            <!-- App Branding -->
            <div class="flex items-center gap-3 px-6 h-16 border-b border-slate-800/80 bg-[#070D19]/60 shrink-0">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-600/15 text-blue-400 border border-blue-500/20 font-bold shadow-xs">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div>
                    <h1 class="font-bold text-white tracking-tight leading-none text-base">{{ __('common.app_name') }}</h1>
                    <span class="text-[11px] text-slate-400 tracking-wide">{{ __('common.app_tagline') }}</span>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto fintech-scrollbar">
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>{{ __('navigation.dashboard') }}</span>
                </a>

                <!-- Stocks Submenu -->
                <div class="space-y-1">
                    <div class="flex items-center gap-3 px-3 pt-3 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span>{{ __('navigation.stocks') }}</span>
                    </div>
                    <div class="ml-4 pl-2 border-l border-slate-800 space-y-1">
                        <a href="{{ route('companies.index') }}"
                           class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-medium transition-all duration-150 {{ request()->routeIs('companies.*') || request()->routeIs('admin.companies.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('companies.*') || request()->routeIs('admin.companies.*') ? 'bg-white' : 'bg-slate-500' }}"></span>
                            <span>{{ __('navigation.companies') }}</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('transactions.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('transactions.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span>{{ __('navigation.transactions') }}</span>
                </a>

                <a href="{{ route('portfolio.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('portfolio.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    <span>{{ __('navigation.portfolio') }}</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('reports.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('navigation.reports') }}</span>
                </a>

                <a href="{{ route('analytics.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('analytics.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>{{ __('navigation.analytics') }}</span>
                </a>

                <a href="{{ route('settings.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 {{ request()->routeIs('settings.*') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('navigation.settings') }}</span>
                </a>
            </nav>

            <!-- Desktop User Info & Bottom Currency Indicator -->
            <div class="p-4 border-t border-slate-800/80 bg-[#070D19]/70 space-y-3 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'bg-purple-950/80 text-purple-300 border border-purple-800' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                        {{ auth()->user()->role ?? 'USER' }}
                    </span>
                </div>
                <div class="pt-2 flex items-center justify-between border-t border-slate-800 text-[11px]">
                    <span class="text-slate-400">{{ __('common.currency') }}:</span>
                    <div class="flex items-center gap-1.5 font-mono font-semibold">
                        <a href="{{ route('currency.switch', 'TWD') }}" class="{{ \App\Support\Currency::getUserCurrency() === 'TWD' ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">NT$ TWD</a>
                        <span class="text-slate-600">|</span>
                        <a href="{{ route('currency.switch', 'USD') }}" class="{{ \App\Support\Currency::getUserCurrency() === 'USD' ? 'text-blue-400 font-bold' : 'text-slate-400 hover:text-white' }}">US$ USD</a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden bg-slate-100/70">
            <!-- Header -->
            <header class="flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-slate-200/90 shadow-xs shrink-0">
                <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                    <!-- Mobile Hamburger Button -->
                    <button type="button"
                            @click="sidebarOpen = true"
                            class="p-2 -ml-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 lg:hidden transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500"
                            aria-label="Open sidebar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <div class="flex items-center gap-2 sm:gap-3 truncate">
                        <span class="text-xs sm:text-sm font-semibold text-slate-500 truncate hidden md:inline">
                            {{ config('app.name', 'Stock Portfolio System') }}
                        </span>
                        @isset($header)
                            <span class="text-slate-300 hidden md:inline">/</span>
                            <div class="text-sm sm:text-base font-semibold text-slate-900 truncate">
                                {{ $header }}
                            </div>
                        @endisset
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    <!-- Currency Selector -->
                    <div class="flex items-center text-xs font-semibold rounded-lg border border-slate-200 bg-slate-100/90 p-0.5" role="group" aria-label="{{ __('common.currency_selector') }}">
                        <a href="{{ route('currency.switch', 'TWD') }}"
                           id="currency-switch-twd"
                           class="px-2.5 py-1 rounded-md transition-all duration-150 {{ \App\Support\Currency::getUserCurrency() === 'TWD' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                           title="Switch to NT$ TWD">
                            NT$ TWD
                        </a>
                        <span class="text-slate-300 text-[10px]">|</span>
                        <a href="{{ route('currency.switch', 'USD') }}"
                           id="currency-switch-usd"
                           class="px-2.5 py-1 rounded-md transition-all duration-150 {{ \App\Support\Currency::getUserCurrency() === 'USD' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                           title="Switch to US$ USD">
                            US$ USD
                        </a>
                    </div>

                    <!-- Language Selector -->
                    <div class="flex items-center text-xs font-semibold rounded-lg border border-slate-200 bg-slate-100/90 p-0.5" role="group" aria-label="Language Selector">
                        <a href="{{ route('locale.switch', 'en') }}"
                           id="lang-switch-en"
                           class="px-2.5 py-1 rounded-md transition-all duration-150 {{ app()->getLocale() === 'en' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                           title="Switch to English">
                            EN
                        </a>
                        <span class="text-slate-300 text-[10px]">|</span>
                        <a href="{{ route('locale.switch', 'zh_TW') }}"
                           id="lang-switch-zhtw"
                           class="px-2.5 py-1 rounded-md transition-all duration-150 {{ app()->getLocale() === 'zh_TW' ? 'bg-white text-blue-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                           title="切換至繁體中文">
                            繁中
                        </a>
                    </div>

                    <div class="text-right hidden xl:block">
                        <span class="text-xs font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                    </div>

                    <!-- Logout Form -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">{{ __('navigation.logout') }}</span>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Page Body -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 fintech-scrollbar">
                @if (session('status'))
                    <div class="mb-5 p-4 rounded-xl bg-blue-50/80 border border-blue-200 text-sm font-medium text-blue-800 flex items-start gap-3 shadow-xs">
                        <svg class="w-5 h-5 text-blue-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="leading-relaxed">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
