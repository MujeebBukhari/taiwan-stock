<x-guest-layout title="{{ __('auth.forgot_password') }} — StockSystem">
    <div class="mb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed">
        {{ __('auth.forgot_password_desc') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('auth.email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('login') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300">
                &larr; {{ __('auth.back_to_login') }}
            </a>

            <x-primary-button>
                {{ __('auth.email_reset_link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
