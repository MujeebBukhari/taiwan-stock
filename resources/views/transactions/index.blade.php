<x-app-layout>
    <x-slot name="header">
        {{ __('transactions.title') }}
    </x-slot>

    <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
        <div class="pb-4 border-b border-slate-200 dark:border-slate-700">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('transactions.stock_transactions') }}</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('transactions.ledger_desc') }}</p>
        </div>

        <div class="py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-slate-900 dark:text-white">{{ __('transactions.transaction_engine') }}</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ __('transactions.engine_desc') }}</p>
        </div>
    </div>
</x-app-layout>
