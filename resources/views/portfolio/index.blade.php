<x-app-layout>
    <x-slot name="header">
        Portfolio
    </x-slot>

    <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="pb-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Portfolio Overview</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Aggregate stock positions, inventory valuations, and realized profits scheduled for Phase 4.</p>
        </div>

        <div class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">Positions & Analytics</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Inventory amount, total realized profit, and analytics charts will be displayed here.</p>
        </div>
    </div>
</x-app-layout>
