<x-app-layout>
    <x-slot name="header">
        Dashboard &bull; Portfolio Overview
    </x-slot>

    <div class="space-y-6">
        <!-- 1. Welcome & Status Banner -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Welcome back, {{ auth()->user()->name }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Central Portfolio Overview &bull; Weighted Average Cost (WAC) Accounting Basis &bull; User Isolated
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' }}">
                        Role: {{ auth()->user()->role ?? 'USER' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        Status: Active
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-600">
                        Accounting: WAC
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Dashboard Quick Actions -->
        <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-3 border-b border-slate-100 dark:border-slate-700/60">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Quick Actions</span>
                <span class="text-[11px] text-slate-400">Navigate to key portfolio modules and trading workflows</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- Add BUY Transaction -->
                <a href="{{ route('companies.index') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 justify-center sm:justify-start">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add BUY Transaction</span>
                </a>

                <!-- Record SELL Transaction -->
                <a href="{{ route('portfolio.index') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-rose-50 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 border border-rose-200 dark:border-rose-900/60 transition-colors justify-center sm:justify-start">
                    <svg class="w-4 h-4 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    <span>Record SELL Transaction</span>
                </a>

                <!-- View Portfolio -->
                <a href="{{ route('portfolio.index') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors justify-center sm:justify-start">
                    <svg class="w-4 h-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    <span>View Portfolio</span>
                </a>

                <!-- View Analytics -->
                <a href="{{ route('analytics.index') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors justify-center sm:justify-start">
                    <svg class="w-4 h-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>View Analytics</span>
                </a>

                <!-- View Reports -->
                <a href="{{ route('reports.index') }}"
                   class="flex items-center gap-2.5 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700/60 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 transition-colors justify-center sm:justify-start">
                    <svg class="w-4 h-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>View Reports</span>
                </a>
            </div>
        </div>

        <!-- 3. Portfolio Summary Cards Grid (6 metrics) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
            <!-- 1. Total Inventory Cost Basis -->
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Inventory Cost Basis
                    </span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                        WAC
                    </span>
                </div>
                <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ number_format((float) $summary['total_inventory_cost'], 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Total active holding cost</p>
            </div>

            <!-- 2. Total Realized Profit / Loss -->
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Realized Profit / Loss
                    </span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                        Closed P&L
                    </span>
                </div>
                @php
                    $totalProfitFloat = (float) $summary['total_realized_profit'];
                @endphp
                <p class="text-xl font-bold font-mono mt-2 {{ $totalProfitFloat > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($totalProfitFloat < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                    {{ $totalProfitFloat > 0 ? '+' : '' }}{{ number_format($totalProfitFloat, 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Cumulative closed trades</p>
            </div>

            <!-- 3. Total Quantity Held -->
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Total Quantity Held
                </span>
                <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ number_format((float) $summary['total_quantity'], 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Aggregate inventory shares</p>
            </div>

            <!-- 4. Active Positions -->
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Active Positions
                </span>
                <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $summary['active_positions_count'] }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Positions with quantity &gt; 0</p>
            </div>

            <!-- 5. Companies Held -->
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Companies Held
                </span>
                <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $summary['companies_held_count'] }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Distinct active stocks</p>
            </div>

            <!-- 6. Total Positions (Historical + Active) -->
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Total Historical Positions
                </span>
                <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                    {{ $summary['total_positions_count'] }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">Includes zero-quantity positions</p>
            </div>
        </div>

        <!-- 4. Middle Row: Monthly Realized P/L Chart & Current Month Activity Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Dashboard Lightweight Chart (2 cols) -->
            <div class="lg:col-span-2 p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Monthly Realized Profit/Loss</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Chronological realized gains and losses from completed SELL transactions (most recent 12 months).
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">
                            Immutable Ledger
                        </span>
                    </div>

                    <div class="mt-4">
                        @if ($realized_profit_chart['has_data'])
                            <div class="h-64 w-full relative">
                                <canvas id="dashboardMonthlyProfitChart"></canvas>
                            </div>
                        @else
                            <div class="h-64 flex flex-col items-center justify-center text-center p-6 bg-slate-50 dark:bg-slate-900/40 rounded-lg border border-dashed border-slate-200 dark:border-slate-700">
                                <svg class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">No Realized Profit/Loss Data Yet</p>
                                <p class="text-[11px] text-slate-400 mt-1 max-w-sm">
                                    Realized gains or losses will be charted here once you execute SELL transactions with closed position profits.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 dark:border-slate-700/60 flex items-center justify-between text-[11px] text-slate-400">
                    <span>* Excludes unrealized paper gains and live market feeds.</span>
                    <a href="{{ route('analytics.index') }}" class="font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">
                        Open Full Analytics &rarr;
                    </a>
                </div>
            </div>

            <!-- Right: Current Month Activity & Realized P/L Summary (1 col) -->
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-700/60">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Recent Activity</h3>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $current_month_activity['month_name'] }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            Current Month
                        </span>
                    </div>

                    <!-- Month Stats List -->
                    <div class="space-y-3 mt-4">
                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                            <div>
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">BUY Orders</span>
                                <p class="text-[11px] text-slate-400">{{ number_format((float) $current_month_activity['buy_amount'], 2) }} invested</p>
                            </div>
                            <span class="text-base font-bold font-mono text-emerald-600 dark:text-emerald-400">
                                {{ $current_month_activity['buy_count'] }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                            <div>
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">SELL Orders</span>
                                <p class="text-[11px] text-slate-400">{{ number_format((float) $current_month_activity['sell_amount'], 2) }} proceeds</p>
                            </div>
                            <span class="text-base font-bold font-mono text-rose-600 dark:text-rose-400">
                                {{ $current_month_activity['sell_count'] }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-700/60">
                            <div>
                                <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Realized P/L (This Month)</span>
                                <p class="text-[11px] text-slate-400">Net closed gains / losses</p>
                            </div>
                            @php
                                $monthProfitFloat = (float) $current_month_activity['realized_profit'];
                            @endphp
                            <span class="text-sm font-bold font-mono {{ $monthProfitFloat > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($monthProfitFloat < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300') }}">
                                {{ $monthProfitFloat > 0 ? '+' : '' }}{{ number_format($monthProfitFloat, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-4 border-t border-slate-100 dark:border-slate-700/60">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 dark:text-slate-400">All-Time Closed P&L:</span>
                        <span class="font-bold font-mono {{ $totalProfitFloat > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($totalProfitFloat < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-700 dark:text-slate-300') }}">
                            {{ $totalProfitFloat > 0 ? '+' : '' }}{{ number_format($totalProfitFloat, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Current Holdings Table (Active Positions Only) -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Current Holdings</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                            {{ $current_holdings->count() }} Active {{ Str::plural('Position', $current_holdings->count()) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Active positions with remaining share inventory (quantity &gt; 0), sorted by inventory cost descending.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('portfolio.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300">
                        <span>View Full Portfolio</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Symbol</th>
                            <th class="py-3 px-4 text-right">Quantity</th>
                            <th class="py-3 px-4 text-right">WAC</th>
                            <th class="py-3 px-4 text-right">Inventory Cost</th>
                            <th class="py-3 px-4 text-right">Realized P/L</th>
                            <th class="py-3 px-4 text-right">Last Transaction</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($current_holdings as $position)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <!-- Company Name linking to Portfolio Position Detail -->
                                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('portfolio.positions.show', $position) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline transition-colors font-semibold">
                                        {{ $position->company->name ?? '—' }}
                                    </a>
                                </td>

                                <!-- Symbol linking to Stock Workspace -->
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    @if ($position->company)
                                        <a href="{{ route('stocks.show', $position->company) }}" class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 hover:border-emerald-500 hover:text-emerald-600 transition-colors">
                                            {{ $position->company->symbol }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- Quantity -->
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $position->quantity, 4) }}
                                </td>

                                <!-- Average / WAC -->
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $position->average_price, 4) }}
                                </td>

                                <!-- Inventory Cost Basis -->
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-bold">
                                    {{ number_format((float) $position->inventory_amount, 2) }}
                                </td>

                                <!-- Realized P/L -->
                                @php
                                    $posProfitFloat = (float) $position->realized_profit;
                                @endphp
                                <td class="py-3 px-4 text-right font-mono font-semibold {{ $posProfitFloat > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($posProfitFloat < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $posProfitFloat > 0 ? '+' : '' }}{{ number_format($posProfitFloat, 2) }}
                                </td>

                                <!-- Last Transaction Date -->
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400 text-[11px] whitespace-nowrap">
                                    {{ $position->last_transaction_at ? $position->last_transaction_at->format('Y-m-d') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">No active holdings in your portfolio</p>
                                    <p class="text-xs text-slate-400 mt-1 mb-4">You currently have no open stock positions with positive share quantities.</p>
                                    <a href="{{ route('companies.index') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Browse Companies &amp; Trade</span>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Recent Transactions Table (Latest 10 records) -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Recent Transactions</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                            Latest {{ $recent_transactions->count() }} of 10
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Permanent immutable record of your latest stock trades (newest first). Completed transactions cannot be edited or deleted.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('companies.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300">
                        <span>Trade Stocks</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4 text-center">Type</th>
                            <th class="py-3 px-4 text-right">Quantity</th>
                            <th class="py-3 px-4 text-right">Price</th>
                            <th class="py-3 px-4 text-right">Amount</th>
                            <th class="py-3 px-4 text-right">Realized Profit/Loss</th>
                            <th class="py-3 px-4">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($recent_transactions as $tx)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <!-- Date -->
                                <td class="py-3 px-4 text-slate-700 dark:text-slate-300 font-mono text-[11px] whitespace-nowrap">
                                    {{ $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '—' }}
                                </td>

                                <!-- Company linking to workspace -->
                                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                    @if ($tx->company)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('stocks.show', $tx->company) }}" class="inline-flex items-center px-1.5 py-0.5 rounded font-mono font-bold text-[11px] bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 hover:border-emerald-500 hover:text-emerald-600 transition-colors">
                                                {{ $tx->company->symbol }}
                                            </a>
                                            <a href="{{ route('stocks.show', $tx->company) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline transition-colors truncate max-w-[160px] sm:max-w-xs">
                                                {{ $tx->company->name }}
                                            </a>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- BUY / SELL Type -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    @if ($tx->type->isBuy())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            BUY
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            SELL
                                        </span>
                                    @endif
                                </td>

                                <!-- Quantity -->
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $tx->quantity, 4) }}
                                </td>

                                <!-- Price -->
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                    {{ number_format((float) $tx->price, 4) }}
                                </td>

                                <!-- Amount -->
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                    {{ number_format((float) $tx->amount, 2) }}
                                </td>

                                <!-- Realized Profit for SELL / Dash for BUY -->
                                <td class="py-3 px-4 text-right font-mono font-semibold">
                                    @if ($tx->type->isSell())
                                        @php
                                            $txProfit = (float) $tx->realized_profit;
                                        @endphp
                                        <span class="{{ $txProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($txProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                            {{ $txProfit > 0 ? '+' : '' }}{{ number_format($txProfit, 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 dark:text-slate-600">—</span>
                                    @endif
                                </td>

                                <!-- Notes -->
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400 max-w-xs truncate text-[11px]">
                                    {{ $tx->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="mx-auto h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">No transactions recorded yet</p>
                                    <p class="text-xs text-slate-400 mt-1 mb-4">Completed immutable transaction records will appear here.</p>
                                    <a href="{{ route('companies.index') }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Record First BUY Transaction</span>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Chart.js Script for Monthly Realized Profit/Loss Chart -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartPayload = @json($realized_profit_chart);
            const canvas = document.getElementById('dashboardMonthlyProfitChart');

            if (!canvas || !chartPayload || !chartPayload.has_data) {
                return;
            }

            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded.');
                return;
            }

            const isDark = document.documentElement.classList.contains('dark') ||
                           window.matchMedia('(prefers-color-scheme: dark)').matches;
            const gridColor = isDark ? 'rgba(51, 65, 85, 0.4)' : 'rgba(226, 232, 240, 0.8)';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: chartPayload.labels,
                    datasets: chartPayload.datasets.map(ds => ({
                        ...ds,
                        borderRadius: 4,
                        barPercentage: 0.6,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.parsed.y;
                                    const formatted = new Intl.NumberFormat('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format(val);
                                    return ' Realized P/L: ' + (val >= 0 ? '+' : '') + formatted;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { size: 11 }
                            }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { size: 11 },
                                callback: function (val) {
                                    return new Intl.NumberFormat('en-US', {
                                        notation: 'compact',
                                        maximumFractionDigits: 1
                                    }).format(val);
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
