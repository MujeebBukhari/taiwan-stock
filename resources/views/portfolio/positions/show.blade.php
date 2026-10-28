<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ __('portfolio.position_detail') }} &bull; {{ $company->symbol }}
            </h1>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline-flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                {{ $company->name }} ({{ $company->exchange }})
            </span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Company Header Banner -->
        <div class="p-6 rounded-xl bg-white shadow-card border border-slate-200/90 transition-all duration-150">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-4">
                    <a href="{{ route('portfolio.index') }}"
                       class="mt-1 sm:mt-0 p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                       title="{{ __('portfolio.back_to_portfolio') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $company->name }}</h2>
                            <span class="px-2.5 py-0.5 rounded-md font-mono text-sm font-bold bg-slate-100 text-slate-900 border border-slate-200">
                                {{ $company->symbol }}
                            </span>
                            @if ($company->is_active)
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ __('common.active') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ __('common.inactive') }}
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-4 mt-2 text-xs text-slate-500 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">{{ __('portfolio.exchange') }}:</span>
                                <span class="text-slate-700 font-semibold">{{ $company->exchange }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">{{ __('portfolio.sector') }}:</span>
                                <span class="text-slate-700 font-semibold">{{ $company->sector ?? __('stocks.unspecified') }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">{{ __('stocks.currency') }}:</span>
                                <span class="font-mono text-slate-700 font-semibold">{{ $company->currency }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Navigation & Trade Action Buttons -->
                <div class="flex items-center gap-2 self-start lg:self-center flex-wrap">
                    <a href="{{ route('stocks.show', $company) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs hover:border-slate-300 transition-colors">
                        <span>{{ __('stocks.workspace') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>

                    <a href="{{ route('stocks.buy.create', $company) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('transactions.buy_stock') }}</span>
                    </a>

                    @if ($position->isHolding())
                        <a href="{{ route('stocks.sell.create', $company) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            <span>{{ __('transactions.sell_stock') }}</span>
                        </a>
                    @else
                        <button type="button"
                                disabled
                                title="{{ __('stocks.no_shares_to_sell') }}"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-slate-400 bg-slate-100 border border-slate-200 cursor-not-allowed opacity-60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            <span>{{ __('transactions.sell_stock') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Current Position Summary Cards (4 cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Current Quantity -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ __('portfolio.current_quantity') }}
                    </span>
                    @if ($position->isHolding())
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ __('portfolio.holding') }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200">
                            {{ __('portfolio.zero') }}
                        </span>
                    @endif
                </div>
                <p class="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ number_format((float) $position->quantity, 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.available_stock_shares') }}</p>
            </div>

            <!-- 2. Weighted Avg Price (WAC) -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('portfolio.weighted_avg_price') }}
                </span>
                <p class="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ \App\Support\Currency::formatConverted($position->average_price, decimals: 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.wac_cost_basis_per_share') }}</p>
            </div>

            <!-- 3. Inventory Cost Basis -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ __('portfolio.inventory_cost_basis') }}
                    </span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200/70">
                        {{ __('portfolio.cost_basis') }}
                    </span>
                </div>
                <p class="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ \App\Support\Currency::formatConverted($position->inventory_amount, decimals: 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.total_current_acq_cost') }}</p>
            </div>

            <!-- 4. Cumulative Realized Profit -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('portfolio.realized_profit') }}
                </span>
                @php
                    $isPosProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $position->realized_profit, '0');
                    $isPosProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $position->realized_profit, '0');
                @endphp
                <p class="text-2xl font-bold font-mono tabular-nums mt-2 tracking-tight {{ $isPosProfPos ? 'text-emerald-600' : ($isPosProfNeg ? 'text-rose-600' : 'text-slate-900') }}">
                    {{ \App\Support\Currency::formatConverted($position->realized_profit, decimals: 2, showPlusSign: true) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.cumulative_profit_sales') }}</p>
            </div>
        </div>

        <!-- Transaction Summary Cards Grid -->
        <div class="p-6 rounded-xl bg-white shadow-card border border-slate-200/90">
            <div class="pb-4 mb-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('portfolio.historical_totals') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('portfolio.historical_totals_desc', ['symbol' => $company->symbol]) }}
                    </p>
                </div>
                <span class="text-xs text-slate-500 font-mono tabular-nums">
                    {{ __('portfolio.last_transaction') }}: {{ $position->last_transaction_at ? $position->last_transaction_at->format('Y-m-d') : '—' }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Total BUY Quantity -->
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <span class="block text-[11px] font-semibold uppercase text-slate-500">{{ __('portfolio.total_buy_qty') }}</span>
                    <span class="text-base font-bold font-mono tabular-nums text-slate-900 mt-1 block">
                        {{ number_format((float) $transactionSummary['total_buy_quantity'], 4) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('portfolio.buy_orders_count', ['count' => $transactionSummary['buy_transactions_count']]) }}</span>
                </div>

                <!-- Total BUY Amount -->
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <span class="block text-[11px] font-semibold uppercase text-slate-500">{{ __('portfolio.total_buy_capital') }}</span>
                    <span class="text-base font-bold font-mono tabular-nums text-slate-900 mt-1 block">
                        {{ \App\Support\Currency::formatConverted($transactionSummary['total_buy_amount'], decimals: 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('portfolio.gross_purchase_vol') }}</span>
                </div>

                <!-- Total SELL Quantity -->
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <span class="block text-[11px] font-semibold uppercase text-slate-500">{{ __('portfolio.total_sell_qty') }}</span>
                    <span class="text-base font-bold font-mono tabular-nums text-slate-900 mt-1 block">
                        {{ number_format((float) $transactionSummary['total_sell_quantity'], 4) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('portfolio.sell_orders_count', ['count' => $transactionSummary['sell_transactions_count']]) }}</span>
                </div>

                <!-- Total SELL Amount -->
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <span class="block text-[11px] font-semibold uppercase text-slate-500">{{ __('portfolio.total_sell_revenue') }}</span>
                    <span class="text-base font-bold font-mono tabular-nums text-slate-900 mt-1 block">
                        {{ \App\Support\Currency::formatConverted($transactionSummary['total_sell_amount'], decimals: 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('portfolio.gross_sale_proceeds') }}</span>
                </div>

                <!-- Total SELL Cost Basis -->
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <span class="block text-[11px] font-semibold uppercase text-slate-500">{{ __('portfolio.total_sold_cost') }}</span>
                    <span class="text-base font-bold font-mono tabular-nums text-slate-900 mt-1 block">
                        {{ \App\Support\Currency::formatConverted($transactionSummary['total_sell_cost'], decimals: 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('portfolio.wac_cost_consumed') }}</span>
                </div>

                <!-- Total Realized Profit -->
                <div class="p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                    <span class="block text-[11px] font-semibold uppercase text-slate-500">{{ __('portfolio.closed_trades_pl') }}</span>
                    @php
                        $isClosedProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $transactionSummary['total_realized_profit'], '0');
                        $isClosedProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $transactionSummary['total_realized_profit'], '0');
                    @endphp
                    <span class="text-base font-bold font-mono tabular-nums mt-1 block {{ $isClosedProfPos ? 'text-emerald-600' : ($isClosedProfNeg ? 'text-rose-600' : 'text-slate-900') }}">
                        {{ \App\Support\Currency::formatConverted($transactionSummary['total_realized_profit'], decimals: 2, showPlusSign: true) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('portfolio.net_realized_profit') }}</span>
                </div>
            </div>
        </div>

        <!-- Realized Profit Breakdown (SELL Transactions) -->
        <div class="rounded-xl bg-white shadow-card border border-slate-200/90 overflow-hidden">
            <div class="p-5 border-b border-slate-200/80">
                <h3 class="text-base font-bold text-slate-900">{{ __('portfolio.realized_profit_history') }}</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('portfolio.realized_profit_history_desc') }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">{{ __('transactions.date') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('portfolio.quantity_sold') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('portfolio.selling_price') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('portfolio.selling_amount') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('transactions.cost') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('portfolio.realized_profit') }}</th>
                            <th class="py-3 px-4">{{ __('transactions.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sellTransactions as $sellTx)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-slate-600 font-mono tabular-nums text-[11px] whitespace-nowrap">
                                    {{ $sellTx->transaction_date->format('Y-m-d') }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ number_format((float) $sellTx->quantity, 4) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ \App\Support\Currency::formatConverted($sellTx->price, decimals: 4) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-semibold">
                                    {{ \App\Support\Currency::formatConverted($sellTx->amount, decimals: 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-600">
                                    {{ \App\Support\Currency::formatConverted($sellTx->cost, decimals: 2) }}
                                </td>
                                @php
                                    $isTxProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $sellTx->realized_profit, '0');
                                    $isTxProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $sellTx->realized_profit, '0');
                                @endphp
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums font-bold {{ $isTxProfPos ? 'text-emerald-600' : ($isTxProfNeg ? 'text-rose-600' : 'text-slate-500') }}">
                                    {{ \App\Support\Currency::formatConverted($sellTx->realized_profit, decimals: 2, showPlusSign: true) }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $sellTx->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500">
                                    <p class="text-xs font-semibold text-slate-700">{{ __('portfolio.no_sell_transactions') }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('portfolio.sell_history_desc') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Complete Transaction History Ledger -->
        <div class="rounded-xl bg-white shadow-card border border-slate-200/90 overflow-hidden">
            <div class="p-5 border-b border-slate-200/80 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ __('portfolio.complete_transaction_ledger') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('portfolio.complete_ledger_desc', ['symbol' => $company->symbol]) }}
                    </p>
                </div>
                <span class="text-xs font-mono tabular-nums font-semibold text-slate-500">
                    {{ __('stocks.total_records', ['count' => $transactions->total()]) }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
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
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($transactions as $transaction)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-slate-600 font-mono tabular-nums text-[11px] whitespace-nowrap">
                                    {{ $transaction->transaction_date->format('Y-m-d') }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($transaction->type->isBuy())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            {{ __('transactions.buy_action') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                            {{ __('transactions.sell_action') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ number_format((float) $transaction->quantity, 4) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ \App\Support\Currency::formatConverted($transaction->price, decimals: 4) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-semibold">
                                    {{ \App\Support\Currency::formatConverted($transaction->amount, decimals: 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-600">
                                    {{ \App\Support\Currency::formatConverted($transaction->cost, decimals: 2) }}
                                </td>
                                @php
                                    $isLedgerProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $transaction->realized_profit, '0');
                                    $isLedgerProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $transaction->realized_profit, '0');
                                @endphp
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold {{ $isLedgerProfPos ? 'text-emerald-600' : ($isLedgerProfNeg ? 'text-rose-600' : 'text-slate-500') }}">
                                    {{ \App\Support\Currency::formatConverted($transaction->realized_profit, decimals: 2, showPlusSign: true) }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $transaction->notes ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <a href="{{ route('stocks.transactions.edit', [$company, $transaction]) }}"
                                       class="inline-flex items-center px-2.5 py-1 rounded-md text-[11px] font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs hover:border-slate-300 transition-colors">
                                        <svg class="w-3 h-3 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        {{ __('common.edit') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-500">
                                    <p class="text-sm font-semibold text-slate-800">{{ __('stocks.no_transactions_yet') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
