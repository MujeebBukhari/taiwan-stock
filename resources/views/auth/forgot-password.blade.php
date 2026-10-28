<x-guest-layout title="{{ __('auth.forgot_password') }} — StockSystem">
    <div class="mb-6">
        <div class="flex items-center gap-2.5 mb-2">
            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-200/60">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('auth.forgot_password') }}</h2>
        </div>
        <p class="text-xs text-slate-500 leading-relaxed">
            {{ __('auth.forgot_password_desc') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="block mt-1 w-full text-xs" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1 transition-colors">
                &larr; {{ __('auth.back_to_login') }}
            </a>

            <x-primary-button>
                {{ __('auth.email_reset_link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
