<x-app-layout>
    <x-slot name="header">
        {{ __('analytics.title') }} &bull; {{ __('analytics.visual_ledger_desc') }}
    </x-slot>

    <div class="space-y-6">
        <!-- Analytics Header & Filter Panel -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('analytics.visual_analytics') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ __('analytics.visual_analytics_desc') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700">
                        {{ __('analytics.accounting_basis_wac') }}
                    </span>
                </div>
            </div>

            @if ($errors->any())
                <div class="mt-4 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-700 dark:text-red-300">
                    <p class="font-bold mb-1">{{ __('analytics.filter_validation_error') }}</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filter Controls Form -->
            <form method="GET" action="{{ route('analytics.index') }}" class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Date From -->
                <div>
                    <label for="date_from" class="block text-[11px] font-semibold text-slate-600 dark:text-slate-300 mb-1">
                        {{ __('analytics.from_date') }}
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
                        {{ __('analytics.to_date') }}
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
                        {{ __('common.company') }}
                    </label>
                    <select id="company_id"
                            name="company_id"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">{{ __('analytics.all_companies') }}</option>
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
                        {{ __('companies.exchange') }}
                    </label>
                    <select id="exchange"
                            name="exchange"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">{{ __('analytics.all_exchanges') }}</option>
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
                        {{ __('companies.sector') }}
                    </label>
                    <select id="sector"
                            name="sector"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">{{ __('analytics.all_sectors') }}</option>
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
                        {{ __('analytics.tx_type') }}
                    </label>
                    <div class="flex items-center gap-1.5">
                        <select id="type"
                                name="type"
                                class="flex-1 px-3 py-1.5 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('analytics.all_types') }}</option>
                            <option value="BUY" {{ strtoupper($filters['type'] ?? '') === 'BUY' ? 'selected' : '' }}>{{ __('analytics.buy_only') }}</option>
                            <option value="SELL" {{ strtoupper($filters['type'] ?? '') === 'SELL' ? 'selected' : '' }}>{{ __('analytics.sell_only') }}</option>
                        </select>

                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg transition-colors">
                            {{ __('analytics.apply') }}
                        </button>

                        @if (!empty($filters['date_from']) || !empty($filters['date_to']) || !empty($filters['company_id']) || !empty($filters['exchange']) || !empty($filters['sector']) || (($filters['type'] ?? 'all') !== 'all'))
                            <a href="{{ route('analytics.index') }}"
                               title="{{ __('analytics.reset_filters') }}"
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

        <!-- 1. Top Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('analytics.buy_orders') }}</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                    {{ $summaryMetrics['total_buy_transactions'] }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('analytics.purchases_executed') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('analytics.sell_orders') }}</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                    {{ $summaryMetrics['total_sell_transactions'] }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('analytics.sales_executed') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('analytics.realized_pl') }}</span>
                @php $totProf = (float) $summaryMetrics['total_realized_profit']; @endphp
                <span class="text-xl font-bold font-mono mt-1 block {{ $totProf > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($totProf < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                    {{ $totProf > 0 ? '+' : '' }}{{ number_format($totProf, 2) }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('analytics.closed_positions_pl') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('analytics.inventory_cost_basis') }}</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                    {{ number_format((float) $summaryMetrics['total_inventory_cost'], 2) }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('analytics.active_acquisition_cost') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('analytics.active_positions') }}</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                    {{ $summaryMetrics['active_positions_count'] }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('analytics.holdings_qty_gt_zero') }}</span>
            </div>

            <div class="p-4 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('analytics.companies_held') }}</span>
                <span class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-1 block">
                    {{ $summaryMetrics['companies_held_count'] }}
                </span>
                <span class="text-[10px] text-slate-400 block mt-0.5">{{ __('analytics.distinct_stocks_tracked') }}</span>
            </div>
        </div>

        <!-- 2. Charts Row 1: Monthly Trading Activity & Monthly Realized Profit -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Trading Activity -->
            <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('analytics.monthly_trading_activity') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('analytics.monthly_trading_desc') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 h-72 relative">
                    @if ($monthlyTradingActivity['has_data'])
                        <canvas id="monthlyActivityChart"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-center text-slate-400">
                            <svg class="w-10 h-10 mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <p class="text-xs font-semibold">{{ __('analytics.no_monthly_tx') }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('analytics.no_monthly_tx_desc') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Monthly Realized Profit / Loss -->
            <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('analytics.monthly_realized_pl') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('analytics.monthly_realized_desc') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 h-72 relative">
                    @if ($monthlyRealizedProfit['has_data'])
                        <canvas id="monthlyRealizedProfitChart"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-center text-slate-400">
                            <svg class="w-10 h-10 mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            <p class="text-xs font-semibold">{{ __('analytics.no_monthly_pl') }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('analytics.no_monthly_pl_desc') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Charts Row 2: Realized Profit by Company & Inventory Cost Basis Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Realized Profit by Company -->
            <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('analytics.realized_pl_by_company') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('analytics.realized_pl_company_desc') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 h-72 relative">
                    @if ($companyRealizedProfit['has_data'])
                        <canvas id="companyRealizedProfitChart"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-center text-slate-400">
                            <svg class="w-10 h-10 mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-xs font-semibold">{{ __('analytics.no_closed_positions') }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('analytics.no_closed_positions_desc') }}</p>
                        </div>
                    @endif
                </div>

                @if ($companyRealizedProfit['has_data'])
                    <div class="mt-4 overflow-x-auto max-h-48 overflow-y-auto border-t border-slate-100 dark:border-slate-700/60 pt-3">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 uppercase text-[10px] border-b border-slate-200 dark:border-slate-700 pb-1">
                                    <th class="py-1">{{ __('common.symbol') }}</th>
                                    <th class="py-1">{{ __('common.company') }}</th>
                                    <th class="py-1 text-center">{{ __('analytics.orders') }}</th>
                                    <th class="py-1 text-right">{{ __('analytics.revenue') }}</th>
                                    <th class="py-1 text-right">{{ __('analytics.cost_basis') }}</th>
                                    <th class="py-1 text-right">{{ __('analytics.profit_loss') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach ($companyRealizedProfit['table_data'] as $cItem)
                                    @php $cProf = (float) $cItem['realized_profit']; @endphp
                                    <tr>
                                        <td class="py-1.5 font-bold font-mono">{{ $cItem['symbol'] }}</td>
                                        <td class="py-1.5 text-slate-600 dark:text-slate-300 truncate max-w-xs">{{ $cItem['company_name'] }}</td>
                                        <td class="py-1.5 text-center font-mono">{{ $cItem['sell_transactions_count'] }}</td>
                                        <td class="py-1.5 text-right font-mono">{{ number_format((float) $cItem['total_sell_revenue'], 2) }}</td>
                                        <td class="py-1.5 text-right font-mono">{{ number_format((float) $cItem['total_sell_cost_basis'], 2) }}</td>
                                        <td class="py-1.5 text-right font-mono font-bold {{ $cProf > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($cProf < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500') }}">
                                            {{ $cProf > 0 ? '+' : '' }}{{ number_format($cProf, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- Inventory Cost Basis by Company -->
            <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('analytics.inventory_cost_basis_by_company') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ __('analytics.inventory_cost_company_desc') }}
                        </p>
                    </div>
                    <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                        {{ __('common.total') }}: {{ number_format((float) $costBasisDistribution['total_cost_basis'], 2) }}
                    </span>
                </div>

                <div class="mt-4 h-72 relative">
                    @if ($costBasisDistribution['has_data'])
                        <canvas id="costBasisChart"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-center text-slate-400">
                            <svg class="w-10 h-10 mb-2 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                            <p class="text-xs font-semibold">{{ __('analytics.no_active_positions') }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('analytics.no_active_positions_desc') }}</p>
                        </div>
                    @endif
                </div>

                @if ($costBasisDistribution['has_data'])
                    <div class="mt-4 overflow-x-auto max-h-48 overflow-y-auto border-t border-slate-100 dark:border-slate-700/60 pt-3">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-slate-400 uppercase text-[10px] border-b border-slate-200 dark:border-slate-700 pb-1">
                                    <th class="py-1">{{ __('common.symbol') }}</th>
                                    <th class="py-1">{{ __('common.company') }}</th>
                                    <th class="py-1 text-right">{{ __('analytics.shares') }}</th>
                                    <th class="py-1 text-right">{{ __('analytics.avg_price_wac') }}</th>
                                    <th class="py-1 text-right">{{ __('analytics.cost_basis') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @foreach ($costBasisDistribution['table_data'] as $posItem)
                                    <tr>
                                        <td class="py-1.5 font-bold font-mono">{{ $posItem['symbol'] }}</td>
                                        <td class="py-1.5 text-slate-600 dark:text-slate-300 truncate max-w-xs">{{ $posItem['company_name'] }}</td>
                                        <td class="py-1.5 text-right font-mono">{{ number_format((float) $posItem['quantity'], 4) }}</td>
                                        <td class="py-1.5 text-right font-mono">{{ number_format((float) $posItem['average_price'], 2) }}</td>
                                        <td class="py-1.5 text-right font-mono font-bold text-slate-900 dark:text-white">
                                            {{ number_format((float) $posItem['inventory_amount'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- 4. Chart Row 3: Transaction Type Distribution -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('analytics.transaction_distribution') }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('analytics.transaction_dist_desc') }}
                    </p>
                </div>
                <span class="text-xs font-mono font-semibold text-slate-500 dark:text-slate-400">
                    {{ __('common.total') }}: {{ $transactionDistribution['total_count'] }} {{ __('analytics.orders_count_suffix') }}
                </span>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
                <div class="h-60 relative md:col-span-1">
                    @if ($transactionDistribution['has_data'])
                        <canvas id="txDistributionChart"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center h-full text-center text-slate-400">
                            <p class="text-xs font-semibold">{{ __('analytics.no_tx_records') }}</p>
                        </div>
                    @endif
                </div>

                <div class="md:col-span-2 grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider">{{ __('analytics.buy_transactions') }}</span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        </div>
                        <span class="text-2xl font-bold font-mono text-emerald-900 dark:text-emerald-100 mt-2 block">
                            {{ $transactionDistribution['buy_count'] }}
                        </span>
                        @php
                            $buyPct = $transactionDistribution['total_count'] > 0
                                ? round(($transactionDistribution['buy_count'] / $transactionDistribution['total_count']) * 100, 1)
                                : 0;
                        @endphp
                        <span class="text-xs text-emerald-700 dark:text-emerald-400 mt-1 block">
                            {{ $buyPct }}{{ __('analytics.pct_of_total_volume') }}
                        </span>
                    </div>

                    <div class="p-4 rounded-lg bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-800 dark:text-rose-300 uppercase tracking-wider">{{ __('analytics.sell_transactions') }}</span>
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                        </div>
                        <span class="text-2xl font-bold font-mono text-rose-900 dark:text-rose-100 mt-2 block">
                            {{ $transactionDistribution['sell_count'] }}
                        </span>
                        @php
                            $sellPct = $transactionDistribution['total_count'] > 0
                                ? round(($transactionDistribution['sell_count'] / $transactionDistribution['total_count']) * 100, 1)
                                : 0;
                        @endphp
                        <span class="text-xs text-rose-700 dark:text-rose-400 mt-1 block">
                            {{ $sellPct }}{{ __('analytics.pct_of_total_volume') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded.');
                return;
            }

            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.06)';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // 1. Monthly Trading Activity Chart
            const monthlyActivityCanvas = document.getElementById('monthlyActivityChart');
            if (monthlyActivityCanvas) {
                const activityData = @json($monthlyTradingActivity);
                new Chart(monthlyActivityCanvas, {
                    type: 'bar',
                    data: {
                        labels: activityData.labels,
                        datasets: activityData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { color: textColor, font: { size: 11, weight: 'bold' } }
                            },
                            tooltip: { mode: 'index', intersect: false }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 10 } }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, stepSize: 1, font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            // 2. Monthly Realized Profit Chart
            const monthlyProfitCanvas = document.getElementById('monthlyRealizedProfitChart');
            if (monthlyProfitCanvas) {
                const profitData = @json($monthlyRealizedProfit);
                new Chart(monthlyProfitCanvas, {
                    type: 'bar',
                    data: {
                        labels: profitData.labels,
                        datasets: profitData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const val = context.parsed.y;
                                        return (val >= 0 ? '+' : '') + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 10 } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return (value >= 0 ? '+' : '') + value.toLocaleString('en-US');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Company Realized Profit Chart
            const companyProfitCanvas = document.getElementById('companyRealizedProfitChart');
            if (companyProfitCanvas) {
                const cProfitData = @json($companyRealizedProfit);
                new Chart(companyProfitCanvas, {
                    type: 'bar',
                    data: {
                        labels: cProfitData.labels,
                        datasets: cProfitData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const val = context.parsed.y;
                                        return (val >= 0 ? '+' : '') + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor },
                                ticks: { color: textColor, font: { size: 10, weight: 'bold' } }
                            },
                            y: {
                                grid: { color: gridColor },
                                ticks: {
                                    color: textColor,
                                    font: { size: 10 },
                                    callback: function(value) {
                                        return (value >= 0 ? '+' : '') + value.toLocaleString('en-US');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 4. Inventory Cost Basis by Company Chart
            const costBasisCanvas = document.getElementById('costBasisChart');
            if (costBasisCanvas) {
                const costBasisData = @json($costBasisDistribution);
                new Chart(costBasisCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: costBasisData.labels,
                        datasets: costBasisData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { color: textColor, font: { size: 10, weight: 'bold' }, boxWidth: 12 }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const val = context.parsed;
                                        return context.label + ': ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        cutout: '65%'
                    }
                });
            }

            // 5. Transaction Distribution Chart
            const txDistCanvas = document.getElementById('txDistributionChart');
            if (txDistCanvas) {
                const txData = @json($transactionDistribution);
                new Chart(txDistCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: txData.labels,
                        datasets: txData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.label + ': ' + context.parsed + ' orders';
                                    }
                                }
                            }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
</x-app-layout>
