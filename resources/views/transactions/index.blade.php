<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ __('transactions.title') }}
            </h1>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline-flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                {{ __('transactions.ledger_desc') }}
            </span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Main Landing Card -->
        <div class="p-6 sm:p-8 rounded-xl bg-white shadow-card border border-slate-200/90 transition-all duration-150">
            <div class="pb-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-slate-900">{{ __('transactions.stock_transactions') }}</h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('transactions.ledger_desc') }}</p>
                </div>
                <span class="px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                    {{ __('common.wac') }} {{ __('common.accounting') }}
                </span>
            </div>

            <!-- Central Hero / Illustration Block -->
            <div class="py-12 px-4 text-center max-w-xl mx-auto">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 shadow-xs">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                    {{ __('transactions.transaction_engine') }}
                </h3>

                <p class="mt-2 text-xs text-slate-500 leading-relaxed">
                    {{ __('transactions.engine_desc') }}
                </p>

                <!-- Action Pathways -->
                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                    <a href="{{ route('companies.index') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('dashboard.browse_companies') }}</span>
                    </a>

                    <a href="{{ route('portfolio.index') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs hover:border-slate-300 transition-all duration-150">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                        <span>{{ __('dashboard.view_portfolio') }}</span>
                    </a>
                </div>
            </div>

            <!-- Context Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-6 border-t border-slate-100">
                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <h4 class="text-xs font-bold text-slate-900">{{ __('transactions.trade_details') }}</h4>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-normal">
                        Every BUY accumulates inventory and recalculates the Weighted Average Cost (WAC) per share.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h4 class="text-xs font-bold text-slate-900">{{ __('transactions.realized_profit') }}</h4>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-normal">
                        Every SELL permanently locks in realized profit or loss against current WAC cost basis.
                    </p>
                </div>

                <div class="p-4 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                        <h4 class="text-xs font-bold text-slate-900">{{ __('dashboard.immutable_ledger') }}</h4>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-normal">
                        All transactions are user-isolated, auditable, and maintain full chronological integrity.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
