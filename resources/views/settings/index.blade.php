<x-app-layout>
    <x-slot name="header">
        {{ __('settings.title') }}
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <!-- Application Diagnostics & Configuration Card -->
        <div class="p-6 rounded-xl bg-white border border-slate-200/90 shadow-card">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('settings.app_configuration') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('settings.app_config_desc') }}</p>
                    </div>
                </div>

                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 me-1.5"></span>
                    Operational
                </span>
            </div>

            <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 shadow-xs">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('settings.environment') }}</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1 flex items-center gap-1.5">
                        <span class="capitalize">{{ app()->environment() }}</span>
                    </dd>
                </div>

                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 shadow-xs">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('settings.database_driver') }}</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">
                        {{ config('database.default') }} <span class="text-xs text-slate-500 font-normal">(WAL Mode)</span>
                    </dd>
                </div>

                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 shadow-xs">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('settings.math_engine') }}</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1">
                        PHP BCMath <span class="text-xs text-slate-500 font-normal">(8 dec)</span>
                    </dd>
                </div>

                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80 shadow-xs">
                    <dt class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('settings.session_driver') }}</dt>
                    <dd class="text-sm font-bold text-slate-900 mt-1 capitalize">{{ config('session.driver') }}</dd>
                </div>
            </dl>
        </div>

        <!-- User Profile Shortcut Card -->
        <div class="p-6 rounded-xl bg-white border border-slate-200/90 shadow-card">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-blue-50 text-blue-600 border border-blue-200/60">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">{{ __('settings.user_profile') }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('settings.user_profile_desc') }}</p>
                    </div>
                </div>

                <a href="{{ route('profile.edit') }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-xs hover:shadow transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    <span>{{ __('settings.edit_profile_btn') }}</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
