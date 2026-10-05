<x-app-layout>
    <x-slot name="header">
        Stocks Management
    </x-slot>

    <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Stock / Company Master</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Scheduled for implementation in Phase 2.</p>
            </div>
            @can('manage-stocks')
                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">
                    Admin Access Granted
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                    Read Only
                </span>
            @endcan
        </div>

        <div class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Stock Master Workspace</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Companies, symbols, exchanges, and sectors will be managed here.</p>
        </div>
    </div>
</x-app-layout>
