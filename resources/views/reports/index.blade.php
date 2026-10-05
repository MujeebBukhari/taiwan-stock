<x-app-layout>
    <x-slot name="header">
        Reports
    </x-slot>

    <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="pb-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Reports & Exports</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Filterable transaction, inventory, and profit reports scheduled for Phase 5.</p>
        </div>

        <div class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Financial Reporting</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">PDF and Excel export generators will be available here.</p>
        </div>
    </div>
</x-app-layout>
