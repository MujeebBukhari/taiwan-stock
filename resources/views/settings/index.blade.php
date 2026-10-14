<x-app-layout>
    <x-slot name="header">
        {{ __('settings.title') }}
    </x-slot>

    <div class="space-y-6">
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('settings.app_configuration') }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('settings.app_config_desc') }}</p>

            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('settings.environment') }}</dt>
                    <dd class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ app()->environment() }}</dd>
                </div>
                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('settings.database_driver') }}</dt>
                    <dd class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ config('database.default') }} (WAL Mode)</dd>
                </div>
                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('settings.math_engine') }}</dt>
                    <dd class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">PHP BCMath (Scale: 8 decimals)</dd>
                </div>
                <div class="p-4 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700">
                    <dt class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ __('settings.session_driver') }}</dt>
                    <dd class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-1">{{ config('session.driver') }}</dd>
                </div>
            </dl>
        </div>

        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('settings.user_profile') }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('settings.user_profile_desc') }}</p>
            <div class="mt-4">
                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-medium bg-slate-800 hover:bg-slate-700 text-white dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                    {{ __('settings.edit_profile_btn') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
