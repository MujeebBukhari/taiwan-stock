<x-app-layout>
    <x-slot name="header">
        Reports &bull; Portfolio Accounting & Analytics
    </x-slot>

    <div class="space-y-6">
        <!-- Reports Banner & Filter Panel -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Historical Financial Reports</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Historical ledger analytics, period breakdowns, and realized profit reports based on immutable transaction records.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                        Accounting Mode: Cost Basis (WAC)
                    </span>
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-4 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-700 dark:text-red-300">
                    <p class="font-bold mb-1">Filter Validation Error:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filter Controls Form -->
            <form method="GET" action="{{ route('reports.index') }}" class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        From Date
                    </label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full px-3 py-1.5 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                <!-- Date To -->
                <div>
                    <label for="date_to" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        To Date
                    </label>
                    <input type="date"
                           id="date_to"
                           name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full px-3 py-1.5 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                <!-- Company Dropdown -->
                <div>
                    <label for="company_id" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Company
                    </label>
                    <select id="company_id"
                            name="company_id"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">All Companies</option>
                        @foreach ($filterOptions['companies'] as $comp)
                            <option value="{{ $comp->id }}" {{ ($filters['company_id'] ?? '') == $comp->id ? 'selected' : '' }}>
                                {{ $comp->symbol }} - {{ $comp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Exchange Dropdown -->
                <div>
                    <label for="exchange" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Exchange
                    </label>
                    <select id="exchange"
                            name="exchange"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">All Exchanges</option>
                        @foreach ($filterOptions['exchanges'] as $exchangeOption)
                            <option value="{{ $exchangeOption }}" {{ ($filters['exchange'] ?? '') === $exchangeOption ? 'selected' : '' }}>
                                {{ $exchangeOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sector Dropdown -->
                <div>
                    <label for="sector" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Sector
                    </label>
                    <select id="sector"
                            name="sector"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">All Sectors</option>
                        @foreach ($filterOptions['sectors'] as $sectorOption)
                            <option value="{{ $sectorOption }}" {{ ($filters['sector'] ?? '') === $sectorOption ? 'selected' : '' }}>
                                {{ $sectorOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Transaction Type & Submit Button -->
                <div>
                    <label for="type" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        Tx Type
                    </label>
                    <div class="flex items-center gap-1.5">
                        <select id="type"
                                name="type"
                                class="flex-1 px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>All Types</option>
                            <option value="BUY" {{ strtoupper($filters['type'] ?? '') === 'BUY' ? 'selected' : '' }}>BUY Only</option>
                            <option value="SELL" {{ strtoupper($filters['type'] ?? '') === 'SELL' ? 'selected' : '' }}>SELL Only</option>
                        </select>

                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg transition-colors">
                            Apply
                        </button>

                        @if (!empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['company_id']) || !empty($filters['exchange']) || !empty($filters['sector']) || (($filters['type'] ?? 'all') !== 'all'))
                            <a href="{{ route('reports.index') }}"
                               title="Reset Filters"
                               class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- 1. Portfolio Summary Report -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="pb-4 mb-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">1. Current Portfolio Status</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Current consolidated holdings based on Weighted Average Cost (WAC) acquisition basis.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Total Inventory Cost Basis -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Inventory Cost Basis</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $portfolioSummary['total_inventory_cost'], 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Total acquisition cost</span>
                </div>

                <!-- Total Quantity -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total Quantity</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $portfolioSummary['total_quantity'], 4) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Shares in inventory</span>
                </div>

                <!-- Active Positions -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Active Positions</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $portfolioSummary['active_positions_count'] }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Holdings with Qty &gt; 0</span>
                </div>

                <!-- Total Positions -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total Positions</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $portfolioSummary['total_positions_count'] }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Active &amp; closed positions</span>
                </div>

                <!-- Companies Held -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Companies Held</span>
                    <span class="text-base font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $portfolioSummary['companies_held_count'] }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Distinct stocks tracked</span>
                </div>

                <!-- Total Realized Profit -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">Total Realized Profit</span>
                    @php
                        $cumProfit = (float) $portfolioSummary['total_realized_profit'];
                    @endphp
                    <span class="text-base font-bold font-mono mt-1 block {{ $cumProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($cumProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                        {{ $cumProfit > 0 ? '+' : '' }}{{ number_format($cumProfit, 2) }}
                    </span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Cumulative closed sales P&L</span>
                </div>
            </div>
        </div>

        <!-- 2. Transaction Activity Report -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="pb-4 mb-4 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">2. Historical Transaction Activity</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Lifetime aggregate volume across BUY and SELL ledger records matching active filters.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">BUY Orders</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $transactionActivity['total_buy_transactions'] }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">SELL Orders</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ $transactionActivity['total_sell_transactions'] }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">BUY Quantity</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionActivity['total_buy_quantity'], 4) }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">SELL Quantity</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionActivity['total_sell_quantity'], 4) }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">BUY Amount</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionActivity['total_buy_amount'], 2) }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">SELL Amount</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionActivity['total_sell_amount'], 2) }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">Sold Cost Basis</span>
                    <span class="text-sm font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                        {{ number_format((float) $transactionActivity['total_sell_cost'], 2) }}
                    </span>
                </div>

                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">Realized Profit</span>
                    @php
                        $actProfit = (float) $transactionActivity['total_realized_profit'];
                    @endphp
                    <span class="text-sm font-bold font-mono mt-1 block {{ $actProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($actProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                        {{ $actProfit > 0 ? '+' : '' }}{{ number_format($actProfit, 2) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 3. Company-Wise Summary Report -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">3. Company-Wise Portfolio Summary</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Trading activity and current position cost basis for each company.
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    {{ count($companyWiseReport) }} Company Record(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Exchange</th>
                            <th class="py-3 px-4 text-center">BUY Tx</th>
                            <th class="py-3 px-4 text-center">SELL Tx</th>
                            <th class="py-3 px-4 text-right">Total BUY Qty</th>
                            <th class="py-3 px-4 text-right">Total SELL Qty</th>
                            <th class="py-3 px-4 text-right">Total BUY Amount</th>
                            <th class="py-3 px-4 text-right">Total SELL Amount</th>
                            <th class="py-3 px-4 text-right">Realized Profit</th>
                            <th class="py-3 px-4 text-right">Current Qty</th>
                            <th class="py-3 px-4 text-right">Cost Basis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($companyWiseReport as $item)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700">
                                            {{ $item['company']->symbol }}
                                        </span>
                                        <span class="font-medium text-slate-900 dark:text-white truncate max-w-xs">{{ $item['company']->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">
                                    {{ $item['company']->exchange }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono">{{ $item['buy_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-center font-mono">{{ $item['sell_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $item['total_buy_quantity'], 4) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $item['total_sell_quantity'], 4) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $item['total_buy_amount'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $item['total_sell_amount'], 2) }}</td>
                                @php
                                    $cpProfit = (float) $item['total_realized_profit'];
                                @endphp
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $cpProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($cpProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $cpProfit > 0 ? '+' : '' }}{{ number_format($cpProfit, 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-semibold text-slate-900 dark:text-slate-100">
                                    {{ number_format((float) $item['current_quantity'], 4) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 dark:text-slate-100">
                                    {{ number_format((float) $item['current_inventory_cost'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-semibold">No company records found matching filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Monthly Activity Report & Visual Trend -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">4. Monthly Historical Activity</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Trading volume, capital flows, and realized profit grouped chronologically by calendar month.
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    {{ count($monthlyReport) }} Month(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Month</th>
                            <th class="py-3 px-4 text-center">BUY Tx</th>
                            <th class="py-3 px-4 text-center">SELL Tx</th>
                            <th class="py-3 px-4 text-right">BUY Quantity</th>
                            <th class="py-3 px-4 text-right">SELL Quantity</th>
                            <th class="py-3 px-4 text-right">BUY Amount</th>
                            <th class="py-3 px-4 text-right">SELL Amount</th>
                            <th class="py-3 px-4 text-right">Realized Profit / (Loss)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($monthlyReport as $m)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white font-mono">
                                    {{ $m['month_name'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono">{{ $m['buy_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-center font-mono">{{ $m['sell_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $m['total_buy_quantity'], 4) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $m['total_sell_quantity'], 4) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $m['total_buy_amount'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $m['total_sell_amount'], 2) }}</td>
                                @php
                                    $mProfit = (float) $m['total_realized_profit'];
                                @endphp
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $mProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($mProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $mProfit > 0 ? '+' : '' }}{{ number_format($mProfit, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-semibold">No monthly transactions found matching filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Annual Summary Report -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">5. Annual Summary</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Year-by-year summary of total trade volume and realized trading results.
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    {{ count($annualReport) }} Calendar Year(s)
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Year</th>
                            <th class="py-3 px-4 text-center">BUY Tx</th>
                            <th class="py-3 px-4 text-center">SELL Tx</th>
                            <th class="py-3 px-4 text-right">BUY Quantity</th>
                            <th class="py-3 px-4 text-right">SELL Quantity</th>
                            <th class="py-3 px-4 text-right">BUY Amount</th>
                            <th class="py-3 px-4 text-right">SELL Amount</th>
                            <th class="py-3 px-4 text-right">Realized Profit / (Loss)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($annualReport as $yr)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white font-mono text-sm">
                                    {{ $yr['year'] }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono">{{ $yr['buy_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-center font-mono">{{ $yr['sell_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $yr['total_buy_quantity'], 4) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $yr['total_sell_quantity'], 4) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $yr['total_buy_amount'], 2) }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100">{{ number_format((float) $yr['total_sell_amount'], 2) }}</td>
                                @php
                                    $yrProfit = (float) $yr['total_realized_profit'];
                                @endphp
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $yrProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($yrProfit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $yrProfit > 0 ? '+' : '' }}{{ number_format($yrProfit, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-semibold">No annual transaction data available matching filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Realized Profit Analysis by Company -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="p-5 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">6. Realized Profit / Loss Analysis by Company</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Breakdown of closed SELL transactions showing revenue, consumed WAC cost basis, and net profit.
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    {{ count($realizedProfitAnalysis) }} Closed Holdings
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Company</th>
                            <th class="py-3 px-4">Symbol</th>
                            <th class="py-3 px-4 text-center">Closed Orders</th>
                            <th class="py-3 px-4 text-right">Total SELL Revenue</th>
                            <th class="py-3 px-4 text-right">Total Cost Basis</th>
                            <th class="py-3 px-4 text-right">Realized Profit / (Loss)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($realizedProfitAnalysis as $rp)
                            @php
                                $profitVal = (float) $rp['realized_profit'];
                            @endphp
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-900 dark:text-white">
                                    {{ $rp['company']->name }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700">
                                        {{ $rp['company']->symbol }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono">{{ $rp['sell_transactions_count'] }}</td>
                                <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                    {{ $rp['company']->currency }} {{ number_format((float) $rp['total_sell_revenue'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-600 dark:text-slate-400">
                                    {{ $rp['company']->currency }} {{ number_format((float) $rp['total_sell_cost_basis'], 2) }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold {{ $profitVal > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($profitVal < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                    {{ $profitVal > 0 ? '+' : '' }}{{ $rp['company']->currency }} {{ number_format($profitVal, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-semibold">No realized profit records found.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Realized profits and losses will populate after SELL transactions are executed.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
