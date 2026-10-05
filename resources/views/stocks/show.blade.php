<x-app-layout>
    <x-slot name="header">
        Stock Workspace &bull; {{ $company->symbol }}
    </x-slot>

    <div class="space-y-6">
        <!-- Company Header Banner -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-4">
                    <a href="{{ route('companies.index') }}"
                       class="mt-1 sm:mt-0 p-2 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                       title="Back to Companies">
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

                <div class="flex items-center gap-2 self-start sm:self-center">
                    <span class="text-xs text-slate-400">Investor:</span>
                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ auth()->user()->name }}</span>
                </div>
            </div>
        </div>

        <!-- Position Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Current Quantity -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Current Quantity
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ number_format((float) ($position?->quantity ?? 0), 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Available stock shares held</p>
            </div>

            <!-- Weighted Average Price (WAC) -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Weighted Avg Price
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $company->currency }} {{ number_format((float) ($position?->average_price ?? 0), 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Cost basis per share</p>
            </div>

            <!-- Inventory Amount -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Inventory Amount
                </span>
                <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $company->currency }} {{ number_format((float) ($position?->inventory_amount ?? 0), 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Total current cost of inventory</p>
            </div>

            <!-- Realized Profit -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Realized Profit
                </span>
                @php
                    $realizedProfit = (float) ($position?->realized_profit ?? 0);
                @endphp
                <p class="text-2xl font-bold font-mono mt-2 {{ $realizedProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($realizedProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-white') }}">
                    {{ $company->currency }} {{ number_format($realizedProfit, 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Cumulative closed trade profit</p>
            </div>
        </div>

        <!-- Transaction History Ledger -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Transaction History Ledger</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Permanent immutable record of all BUY and SELL transactions for {{ $company->symbol }}.
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
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
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
                                <td class="py-3 px-4 text-right font-mono font-semibold {{ (float) $transaction->realized_profit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ((float) $transaction->realized_profit < 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ number_format((float) $transaction->realized_profit, 2) }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                    {{ $transaction->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">No transactions recorded yet</p>
                                    <p class="text-xs text-slate-400 mt-1">Transaction entries will appear here once BUY or SELL orders are executed in Phase 3B.</p>
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
