<x-app-layout>
    <x-slot name="header">
        Position Detail &bull; {{ $company->symbol }}
    </x-slot>

    <div class="space-y-6">
        <!-- Company Header Banner -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-4">
                    <a href="{{ route('portfolio.index') }}"
                       class="mt-1 sm:mt-0 p-2 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                       title="Back to Portfolio">
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
                                    Active
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    Inactive
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center gap-4 mt-2 text-xs text-slate-500 dark:text-slate-400 font-medium">
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">Exchange:</span>
                                <span class="text-slate-700 dark:text-slate-200 font-semibold">{{ $company->exchange }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">Sector:</span>
                                <span class="text-slate-700 dark:text-slate-200 font-semibold">{{ $company->sector ?? 'Unspecified' }}</span>
                            </span>
                            <span>&bull;</span>
                            <span class="flex items-center gap-1">
                                <span class="text-slate-400">Currency:</span>
                                <span class="font-mono text-slate-700 dark:text-slate-200 font-semibold">{{ $company->currency }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Navigation & Trade Action Buttons -->
                <div class="flex items-center gap-2 self-start lg:self-center flex-wrap">
                    <a href="{{ route('stocks.show', $company) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                        <span>Workspace</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>

                    <a href="{{ route('stocks.buy.create', $company) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>BUY Stock</span>
                    </a>

                    @if ($position->isHolding())
                        <a href="{{ route('stocks.sell.create', $company) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-white bg-rose-600 hover:bg-rose-500 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            <span>SELL Stock</span>
                        </a>
                    @else
                        <button type="button"
                                disabled
                                title="Cannot sell: available quantity is zero"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-bold text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-not-allowed opacity-60">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                            <span>SELL Stock</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Current Position Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Current Quantity -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Current Quantity
                    </span>
                    @if ($position->isHolding())
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Holding
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                            Zero
                        </span>
                    @endif
                </div>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ number_format((float) $position->quantity, 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Available stock shares held</p>
            </div>

            <!-- 2. Weighted Avg Price (WAC) -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Weighted Avg Price
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $company->currency }} {{ number_format((float) $position->average_price, 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Weighted average cost basis per share</p>
            </div>

            <!-- 3. Inventory Cost Basis -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Inventory Cost Basis
                    </span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400">
                        Cost Basis
                    </span>
                </div>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $company->currency }} {{ number_format((float) $position->inventory_amount, 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Total current acquisition cost of inventory</p>
            </div>

            <!-- 4. Cumulative Realized Profit -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Realized Profit
                </span>
                @php
                    $realizedProfit = (float) $position->realized_profit;
                @endphp
                <p class="text-2xl font-bold font-mono mt-2 {{ $realizedProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($realizedProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                    {{ $realizedProfit > 0 ? '+' : '' }}{{ $company->currency }} {{ number_format($realizedProfit, 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Cumulative profit from closed sales</p>
            </div>
        </div>

        <!-- Transaction Summary Cards Grid -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="pb-4 mb-4 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Historical Transaction Totals</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Lifetime BUY and SELL volume breakdown for {{ $company->symbol }}. Current holdings are maintained on the position record.
                    </p>
                </div>
                <span class="text-xs text-slate-400 font-mono">
                    Last Transaction: {{ $position->last_transaction_at ? $position->last_transaction_at->format('Y-m-d') : '—' }}
                </span>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Total BUY Quantity -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total BUY Qty</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionSummary['total_buy_quantity'], 4) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transactionSummary['buy_transactions_count'] }} BUY order(s)</span>
                </div>

                <!-- Total BUY Amount -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total BUY Capital</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $company->currency }} {{ number_format((float) $transactionSummary['total_buy_amount'], 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Gross purchase volume</span>
                </div>

                <!-- Total SELL Quantity -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total SELL Qty</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionSummary['total_sell_quantity'], 4) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ $transactionSummary['sell_transactions_count'] }} SELL order(s)</span>
                </div>

                <!-- Total SELL Amount -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total SELL Revenue</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $company->currency }} {{ number_format((float) $transactionSummary['total_sell_amount'], 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Gross sale proceeds</span>
                </div>

                <!-- Total SELL Cost Basis -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total Sold Cost</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $company->currency }} {{ number_format((float) $transactionSummary['total_sell_cost'], 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">WAC cost consumed</span>
                </div>

                <!-- Total Realized Profit -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Closed Trades P&L</span>
                    @php
                        $closedProfit = (float) $transactionSummary['total_realized_profit'];
                    @endphp
                    <span class="text-base font-bold font-mono mt-1 block {{ $closedProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($closedProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                        {{ $closedProfit > 0 ? '+' : '' }}{{ $company->currency }} {{ number_format($closedProfit, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Net realized profit</span>
                </div>
            </div>
        </div>

        <!-- Realized Profit Breakdown (SELL Transactions) -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Realized Profit History</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Breakdown of each completed SELL order showing revenue, consumed WAC cost basis, and resulting profit or loss.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-right">Quantity Sold</th>
                            <th class="py-3 px-4 text-right">Selling Price</th>
                            <th class="py-3 px-4 text-right">Selling Amount</th>
                            <th class="py-3 px-4 text-right">Cost Basis</th>
                            <th class="py-3 px-4 text-right">Realized Profit / (Loss)</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($sellTransactions as $sellTx)
                            @php
                                $txProfit = (float) $sellTx->realized_profit;
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-mono text-[11px] whitespace-nowrap">
                                    {{ $sellTx->transaction_date->format('Y-m-d') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $sellTx->quantity, 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ $company->currency }} {{ number_format((float) $sellTx->price, 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                    {{ $company->currency }} {{ number_format((float) $sellTx->amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    {{ $company->currency }} {{ number_format((float) $sellTx->cost, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $txProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($txProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $txProfit > 0 ? '+' : '' }}{{ $company->currency }} {{ number_format($txProfit, 2) }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                    {{ $sellTx->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">No SELL transactions executed yet</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Realized profit/loss history will appear here once shares are sold.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Complete Transaction History Ledger -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Complete Transaction Ledger</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Permanent immutable ledger of all BUY and SELL transactions for {{ $company->symbol }}.
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    Total Records: {{ $transactions->total() }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4 text-center">Type</th>
                            <th class="py-3 px-4 text-right">Quantity</th>
                            <th class="py-3 px-4 text-right">Price</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-right">Cost</th>
                            <th class="py-3 px-4 text-right">Realized Profit</th>
                            <th class="py-3 px-4">Notes</th>
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
                                            BUY
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            SELL
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $transaction->quantity, 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $transaction->price, 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                    {{ number_format((float) $transaction->amount, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    {{ number_format((float) $transaction->cost, 2) }}
                                </td>
                                @php
                                    $ledgerProfit = (float) $transaction->realized_profit;
                                @endphp
                                <td class="py-3 px-4 text-right font-mono font-semibold {{ $ledgerProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($ledgerProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $ledgerProfit > 0 ? '+' : '' }}{{ number_format($ledgerProfit, 2) }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                    {{ $transaction->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">No transactions recorded yet</p>
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
