<x-app-layout>
    <x-slot name="header">
        {{ __('stocks.workspace_title', ['symbol' => $company->symbol]) }}
    </x-slot>

    <div class="space-y-6">
        <!-- Company Header Banner -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-4">
                    <a href="{{ route('companies.index') }}"
                       class="mt-1 sm:mt-0 p-2 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                       title="{{ __('stocks.back_to_companies') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>

                    <div>
                        <div class="flex items-center gap-3">
                            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $company->name }}</h2>
                            <span class="px-2.5 py-0.5 rounded font-mono text-sm font-bold bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700">
                                {{ $company->symbol }}
                            </span>
                            @if ($company->is_active)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    {{ __('common.active') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    {{ __('common.inactive') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">{{ __('stocks.exchange') }}:</span>
                                <span class="text-slate-700 dark:text-slate-200 font-semibold">{{ $company->exchange }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">{{ __('stocks.sector') }}:</span>
                                <span class="text-slate-700 dark:text-slate-200 font-semibold">{{ $company->sector ?? __('stocks.unspecified') }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">{{ __('stocks.currency') }}:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-200 font-semibold">{{ $company->currency }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 self-start sm:self-center">
                    <div class="hidden sm:block text-right">
                        <span class="block text-[11px] text-slate-400">{{ __('stocks.investor') }}</span>
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('stocks.buy.create', $company) }}"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ __('stocks.execute_buy') }}</span>
                        </a>

                        @php
                            $hasSharesToSell = $position && \App\Services\Financial\FinancialMath::greaterThan((string) $position->quantity, '0');
                        @endphp

                        @if ($hasSharesToSell)
                            <a href="{{ route('stocks.sell.create', $company) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                                <span>{{ __('stocks.execute_sell') }}</span>
                            </a>
                        @else
                            <button type="button"
                                    disabled
                                    title="{{ __('stocks.no_shares_to_sell') }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-not-allowed opacity-60">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                </svg>
                                <span>{{ __('stocks.execute_sell') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Market Price Banner / Live Quote Card -->
        <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                {{ $company->symbol }} &bull; {{ __('stocks.market_price') }}
                            </span>
                            <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                {{ $company->exchange }}
                            </span>
                        </div>
                        @if ($marketQuote)
                            <div class="flex items-baseline gap-3 mt-1">
                                <span class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 dark:text-white">
                                    {{ $marketQuote->formattedPrice() }}
                                </span>
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold font-mono {{ $marketQuote->isPositiveChange() ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : ($marketQuote->isNegativeChange() ? 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300') }}">
                                    @if ($marketQuote->isPositiveChange())
                                        &uarr;
                                    @elseif ($marketQuote->isNegativeChange())
                                        &darr;
                                    @endif
                                    {{ $marketQuote->formattedChange() }} ({{ $marketQuote->formattedChangePercent() }})
                                </span>
                            </div>
                        @else
                            @if (!empty($company->last_price))
                                <div class="flex items-baseline gap-3 mt-1">
                                    <span class="text-2xl sm:text-3xl font-extrabold font-mono text-slate-900 dark:text-white">
                                        {{ $company->formatCurrency($company->last_price) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold font-mono bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        {{ __('stocks.last_traded_price') }}
                                    </span>
                                </div>
                            @else
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-lg font-bold font-mono text-slate-500 dark:text-slate-400">
                                        {{ __('stocks.market_price_unavailable') }}
                                    </span>
                                    <span class="text-xs text-slate-400">
                                        {{ __('stocks.quote_offline') }}
                                    </span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($marketQuote)
                    <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-slate-500 dark:text-slate-400 font-medium pt-3 md:pt-0 border-t md:border-t-0 border-slate-100 dark:border-slate-700/60">
                        @if ($marketQuote->previousClose)
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">{{ __('stocks.prev_close') }}</span>
                                <span class="font-mono text-slate-700 dark:text-slate-200 font-semibold">
                                    {{ \App\Support\Currency::format($marketQuote->previousClose, $marketQuote->currency) }}
                                </span>
                            </div>
                        @endif
                        @if ($marketQuote->volume)
                            <div>
                                <span class="text-slate-400 block text-[10px] uppercase">{{ __('stocks.volume') }}</span>
                                <span class="font-mono text-slate-700 dark:text-slate-200 font-semibold">
                                    {{ number_format((float) $marketQuote->volume) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase">{{ __('stocks.last_updated') }}</span>
                            <span class="font-mono text-slate-700 dark:text-slate-200 font-semibold">
                                {{ $marketQuote->timestamp ? $marketQuote->timestamp->format('H:i:s d/m/Y') : now()->format('H:i:s d/m/Y') }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Position Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Current Quantity -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ __('stocks.current_quantity') }}
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ number_format((float) ($position?->quantity ?? 0), 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.available_shares_held') }}</p>
            </div>

            <!-- Weighted Average Price (WAC) -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ __('stocks.weighted_avg_price') }}
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ \App\Support\Currency::formatConverted($position?->average_price ?? 0, decimals: 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.cost_basis_per_share') }}</p>
            </div>

            <!-- Inventory Amount -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ __('stocks.inventory_amount') }}
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ \App\Support\Currency::formatConverted($position?->inventory_amount ?? 0, decimals: 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.total_current_cost') }}</p>
            </div>

            <!-- Realized Profit -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    {{ __('stocks.realized_profit') }}
                </span>
                @php
                    $isStockProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) ($position?->realized_profit ?? 0), '0');
                    $isStockProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) ($position?->realized_profit ?? 0), '0');
                @endphp
                <p class="text-2xl font-bold font-mono mt-2 {{ $isStockProfPos ? 'text-emerald-600 dark:text-emerald-400' : ($isStockProfNeg ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white') }}">
                    {{ \App\Support\Currency::formatConverted($position?->realized_profit ?? 0, decimals: 2, showPlusSign: true) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.cumulative_closed_profit') }}</p>
            </div>
        </div>

        <!-- Transaction History Ledger -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('stocks.transaction_history_ledger') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('stocks.permanent_record_desc', ['symbol' => $company->symbol]) }}
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    {{ __('stocks.total_records', ['count' => $transactions->total()]) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">{{ __('transactions.date') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('transactions.type') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('transactions.quantity') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('transactions.price') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('common.amount') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('transactions.cost') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('transactions.realized_profit') }}</th>
                            <th class="py-3 px-4">{{ __('transactions.notes') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('common.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($transactions as $transaction)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-mono text-[11px] whitespace-nowrap">
                                    {{ $transaction->transaction_date->format('Y-m-d') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($transaction->type->isBuy())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ __('transactions.buy_action') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            {{ __('transactions.sell_action') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $transaction->quantity, 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ \App\Support\Currency::formatConverted($transaction->price, decimals: 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                    {{ \App\Support\Currency::formatConverted($transaction->amount, decimals: 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    {{ \App\Support\Currency::formatConverted($transaction->cost, decimals: 2) }}
                                </td>
                                @php
                                    $isStockTxProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $transaction->realized_profit, '0');
                                    $isStockTxProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $transaction->realized_profit, '0');
                                @endphp
                                <td class="py-3 px-4 text-right font-mono font-semibold {{ $isStockTxProfPos ? 'text-emerald-600 dark:text-emerald-400' : ($isStockTxProfNeg ? 'text-red-600 dark:text-red-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ \App\Support\Currency::formatConverted($transaction->realized_profit, decimals: 2, showPlusSign: true) }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                    {{ $transaction->notes ?? '—' }}
                                </td>
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <a href="{{ route('stocks.transactions.edit', [$company, $transaction]) }}"
                                       class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('common.edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('stocks.no_transactions_yet') }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ __('stocks.no_transactions_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
