<x-guest-layout :full-width="true" title="Sign In — StockSystem Portfolio & Ledger">
    <div class="min-h-screen flex flex-col lg:flex-row w-full bg-slate-950 text-slate-100 selection:bg-emerald-500 selection:text-white overflow-x-hidden">

        <!-- ========================================================= -->
        <!-- LEFT PANEL (DESKTOP): BRANDING & PORTFOLIO LEDGER OVERVIEW -->
        <!-- ========================================================= -->
        <div class="hidden lg:flex lg:w-7/12 xl:w-3/5 flex-col justify-between p-10 xl:p-16 border-r border-slate-800/80 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 relative overflow-hidden select-none">
            
            <!-- Ambient Background Glows -->
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <!-- Top Section: App Branding & Status -->
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 shadow-inner">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <span class="block text-xl font-bold tracking-tight text-white leading-tight">StockSystem</span>
                        <span class="block text-xs font-semibold text-emerald-400 tracking-wider">Portfolio & Ledger</span>
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-slate-900/90 text-slate-300 border border-slate-800 shadow-sm backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Accounting Engine Online</span>
                </div>
            </div>

            <!-- Middle Section: Value Proposition & Financial Architecture -->
            <div class="relative z-10 my-auto py-8">
                <!-- Headline -->
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 mb-4">
                        <span>Auditable Financial Ledger</span>
                    </div>
                    <h1 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-tight">
                        Manage your stock portfolio <br>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-cyan-400">with confidence.</span>
                    </h1>
                    <p class="mt-4 text-base text-slate-300 leading-relaxed">
                        A specialized portfolio accounting system with continuous cost valuation, immutable ledger integrity, and auditable realized performance.
                    </p>
                </div>

                <!-- Abstract Financial Chart Graphic -->
                <div class="mt-8 p-6 rounded-2xl bg-slate-900/70 border border-slate-800/80 shadow-2xl backdrop-blur-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Portfolio Valuation Curve</span>
                        </div>
                        <span class="text-xs font-mono text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">WAC Continuous</span>
                    </div>

                    <!-- Clean SVG Vector Chart -->
                    <div class="w-full h-32 relative">
                        <svg class="w-full h-full overflow-visible" viewBox="0 0 500 120" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#10b981" stop-opacity="0.3" />
                                    <stop offset="100%" stop-color="#10b981" stop-opacity="0.0" />
                                </linearGradient>
                                <linearGradient id="lineGradient" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="#059669" />
                                    <stop offset="50%" stop-color="#10b981" />
                                    <stop offset="100%" stop-color="#34d399" />
                                </linearGradient>
                            </defs>
                            <!-- Grid lines -->
                            <line x1="0" y1="20" x2="500" y2="20" stroke="#334155" stroke-width="1" stroke-dasharray="3 3" opacity="0.4" />
                            <line x1="0" y1="60" x2="500" y2="60" stroke="#334155" stroke-width="1" stroke-dasharray="3 3" opacity="0.4" />
                            <line x1="0" y1="100" x2="500" y2="100" stroke="#334155" stroke-width="1" stroke-dasharray="3 3" opacity="0.4" />
                            
                            <!-- Area fill -->
                            <path d="M 0 100 Q 120 85 220 55 T 380 40 T 500 15 L 500 120 L 0 120 Z" fill="url(#chartGradient)" />
                            
                            <!-- Trendline -->
                            <path d="M 0 100 Q 120 85 220 55 T 380 40 T 500 15" fill="none" stroke="url(#lineGradient)" stroke-width="2.5" />
                            
                            <!-- Data nodes -->
                            <circle cx="120" cy="85" r="3.5" fill="#10b981" stroke="#0f172a" stroke-width="2" />
                            <circle cx="220" cy="55" r="3.5" fill="#10b981" stroke="#0f172a" stroke-width="2" />
                            <circle cx="380" cy="40" r="3.5" fill="#10b981" stroke="#0f172a" stroke-width="2" />
                            <circle cx="500" cy="15" r="4.5" fill="#34d399" stroke="#0f172a" stroke-width="2" />
                        </svg>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Strict Lot-Free Accounting
                        </span>
                        <span class="font-mono text-slate-300">Precision: 8 Decimals</span>
                    </div>
                </div>

                <!-- 4 Supporting Architectural Pillars -->
                <div class="grid grid-cols-2 gap-4 mt-8">
                    <!-- Pillar 1: Weighted Average Cost -->
                    <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700/80 transition-colors">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-white">Weighted Average Cost</h2>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">
                            Dynamic 8-decimal inventory cost basis updated on each purchase.
                        </p>
                    </div>

                    <!-- Pillar 2: Portfolio Tracking -->
                    <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700/80 transition-colors">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <div class="p-1.5 rounded-lg bg-teal-500/10 text-teal-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-white">Portfolio Tracking</h2>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">
                            Real-time holdings, capital allocation, and current position sizes.
                        </p>
                    </div>

                    <!-- Pillar 3: Transaction Ledger -->
                    <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700/80 transition-colors">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <div class="p-1.5 rounded-lg bg-cyan-500/10 text-cyan-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-white">Transaction Ledger</h2>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">
                            Immutable double-entry ledger capturing all buy and sell records.
                        </p>
                    </div>

                    <!-- Pillar 4: Reports & Analytics -->
                    <div class="p-4 rounded-xl bg-slate-900/40 border border-slate-800/60 hover:border-slate-700/80 transition-colors">
                        <div class="flex items-center gap-2.5 mb-1.5">
                            <div class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <h2 class="text-sm font-semibold text-white">Reports & Analytics</h2>
                        </div>
                        <p class="text-xs text-slate-400 leading-normal">
                            Auditable realized P/L reports, monthly summaries, and data exports.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom Section: Legal & Compliance Footer -->
            <div class="relative z-10 pt-6 border-t border-slate-800/60 flex items-center justify-between text-xs text-slate-400">
                <span>&copy; {{ date('Y') }} StockSystem. All rights reserved.</span>
                <span class="flex items-center gap-1.5 text-slate-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Audit-Ready Portfolio Architecture
                </span>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- RIGHT PANEL: CENTERED LOGIN CARD & FORM -->
        <!-- ========================================================= -->
        <div class="flex-1 flex flex-col justify-center items-center p-6 sm:p-10 lg:p-12 xl:p-16 relative bg-slate-950">
            
            <!-- Mobile Brand Header (Visible only on < lg screens) -->
            <div class="lg:hidden mb-8 text-center">
                <div class="inline-flex items-center gap-3 mb-2">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div class="text-left">
                        <span class="block text-xl font-bold tracking-tight text-white leading-tight">StockSystem</span>
                        <span class="block text-xs font-semibold text-emerald-400 tracking-wider">Portfolio & Ledger</span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">
                    Manage your stock portfolio with confidence.
                </p>
            </div>

            <!-- Login Card -->
            <div class="w-full max-w-md bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800/90 rounded-2xl shadow-2xl p-8 sm:p-10 relative overflow-hidden text-slate-900 dark:text-slate-100">
                
                <!-- Glowing Emerald Top Highlight Strip -->
                <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600"></div>

                <!-- Card Header -->
                <div class="mb-6">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">{{ __('auth.login') }}</h2>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        {{ __('auth.login_title') }}
                    </p>
                </div>

                <!-- Session Status (e.g. Password reset link dispatched) -->
                <x-auth-session-status class="mb-5" :status="session('status')" />

                <!-- Validation / Authentication Error Banner -->
                @if ($errors->any())
                    <div role="alert" class="mb-5 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-800 dark:text-rose-300 text-sm flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div class="space-y-1">
                            <p class="font-semibold text-rose-900 dark:text-rose-200">{{ __('common.error') }}</p>
                            <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700 dark:text-rose-300">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <!-- Authentication Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email Address Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('auth.email') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206" />
                                </svg>
                            </div>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   autofocus
                                   autocomplete="username"
                                   placeholder="name@example.com"
                                   class="block w-full pl-10 pr-3.5 py-2.5 rounded-xl border {{ $errors->has('email') ? 'border-rose-400 dark:border-rose-600 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 dark:border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }} bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm shadow-sm transition-colors focus:outline-none focus:ring-4">
                        </div>
                    </div>

                    <!-- Password Field with Accessible Visibility Toggle (Alpine.js) -->
                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            {{ __('auth.password') }}
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   type="password"
                                   name="password"
                                   required
                                   autocomplete="current-password"
                                   placeholder="••••••••"
                                   class="block w-full pl-10 pr-11 py-2.5 rounded-xl border {{ $errors->has('password') ? 'border-rose-400 dark:border-rose-600 focus:border-rose-500 focus:ring-rose-500/20' : 'border-slate-300 dark:border-slate-700 focus:border-emerald-500 focus:ring-emerald-500/20' }} bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500 text-sm shadow-sm transition-colors focus:outline-none focus:ring-4">
                            
                            <!-- Toggle Button -->
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    :title="showPassword ? 'Hide password' : 'Show password'"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 focus:outline-none focus:text-emerald-500 transition-colors">
                                <!-- Eye icon (shown when hidden) -->
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <!-- Eye-slash icon (shown when visible) -->
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Options Row: Remember Me & Forgot Password Link -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me"
                                   type="checkbox"
                                   name="remember"
                                   class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500 focus:ring-offset-0 bg-white dark:bg-slate-800 transition">
                            <span class="ms-2 text-sm text-slate-600 dark:text-slate-400">{{ __('auth.remember_me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 focus:outline-none focus:underline transition-colors">
                                {{ __('auth.forgot_password') }}
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit"
                                class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-4 focus:ring-emerald-500/25 shadow-lg shadow-emerald-600/25 transition-all duration-150">
                            <span>{{ __('auth.log_in') }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Footer: Security Guarantee (STRICTLY NO REGISTRATION CTA) -->
                <div class="pt-5 mt-6 border-t border-slate-100 dark:border-slate-800 text-center">
                    <p class="text-xs text-slate-400 dark:text-slate-500 flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Encrypted session & rate-limited authentication</span>
                    </p>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>
