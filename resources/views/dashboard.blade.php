<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ __('dashboard.title') }} &bull; {{ __('dashboard.portfolio_overview') }}
            </h1>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline-flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ __('common.wac') }} {{ __('common.accounting') }}
            </span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- 1. Welcome & Status Banner -->
        <div class="p-6 rounded-xl bg-white shadow-card border border-slate-200/90 transition-all duration-150">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">
                        {{ __('dashboard.welcome_back', ['name' => auth()->user()->name]) }}
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ __('dashboard.subtitle') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                        {{ __('common.role') }}: {{ auth()->user()->role ?? 'USER' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        {{ __('common.status') }}: {{ __('common.active') }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ __('common.accounting') }}: {{ __('common.wac') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 2. Dashboard Quick Actions -->
        <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 pb-3 mb-3 border-b border-slate-100">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">{{ __('dashboard.quick_actions') }}</span>
                <span class="text-[11px] text-slate-400">{{ __('dashboard.quick_actions_desc') }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                <!-- Add BUY Transaction (Primary Professional Blue) -->
                <a href="{{ route('companies.index') }}"
                   class="flex items-center justify-center sm:justify-start gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ __('dashboard.add_buy_transaction') }}</span>
                </a>

                <!-- Record SELL Transaction -->
                <a href="{{ route('portfolio.index') }}"
                   class="flex items-center justify-center sm:justify-start gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all duration-150">
                    <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    <span>{{ __('dashboard.record_sell_transaction') }}</span>
                </a>

                <!-- View Portfolio -->
                <a href="{{ route('portfolio.index') }}"
                   class="flex items-center justify-center sm:justify-start gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all duration-150">
                    <svg class="w-4 h-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                    <span>{{ __('dashboard.view_portfolio') }}</span>
                </a>

                <!-- View Analytics -->
                <a href="{{ route('analytics.index') }}"
                   class="flex items-center justify-center sm:justify-start gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all duration-150">
                    <svg class="w-4 h-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                    <span>{{ __('dashboard.view_analytics') }}</span>
                </a>

                <!-- View Reports -->
                <a href="{{ route('reports.index') }}"
                   class="flex items-center justify-center sm:justify-start gap-2 px-3.5 py-2.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200/90 shadow-xs hover:border-slate-300 transition-all duration-150">
                    <svg class="w-4 h-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('dashboard.view_reports') }}</span>
                </a>
            </div>
        </div>

        <!-- 3. Portfolio Summary Cards Grid (6 metrics) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- 1. Total Inventory Cost Basis -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        {{ __('dashboard.inventory_cost_basis') }}
                    </span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200/70">
                        {{ __('common.wac') }}
                    </span>
                </div>
                <p class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ \App\Support\Currency::formatConverted($summary['total_inventory_cost'], decimals: 2) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    {{ __('dashboard.total_active_holding_cost') }}
                </p>
            </div>

            <!-- 2. Total Realized Profit / Loss -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                        {{ __('dashboard.realized_profit_loss') }}
                    </span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200/70">
                        {{ __('dashboard.closed_pl') }}
                    </span>
                </div>
                @php
                    $isProfitPos = \App\Services\Financial\FinancialMath::greaterThan((string) $summary['total_realized_profit'], '0');
                    $isProfitNeg = \App\Services\Financial\FinancialMath::lessThan((string) $summary['total_realized_profit'], '0');
                @endphp
                <p class="text-xl sm:text-2xl font-bold font-mono tabular-nums mt-2 tracking-tight {{ $isProfitPos ? 'text-emerald-600' : ($isProfitNeg ? 'text-rose-600' : 'text-slate-900') }}">
                    {{ \App\Support\Currency::formatConverted($summary['total_realized_profit'], decimals: 2, showPlusSign: true) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('dashboard.cumulative_closed_trades') }}</p>
            </div>

            <!-- 3. Total Quantity Held -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('dashboard.total_quantity_held') }}
                </span>
                <p class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ number_format((float) $summary['total_quantity'], 4) }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('dashboard.aggregate_inventory_shares') }}</p>
            </div>

            <!-- 4. Active Positions -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('dashboard.active_positions') }}
                </span>
                <p class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ $summary['active_positions_count'] }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('dashboard.positions_gt_zero') }}</p>
            </div>

            <!-- 5. Companies Held -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('dashboard.companies_held') }}
                </span>
                <p class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ $summary['companies_held_count'] }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('dashboard.distinct_active_stocks') }}</p>
            </div>

            <!-- 6. Total Positions (Historical + Active) -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 hover:border-slate-300 transition-all duration-150">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                    {{ __('dashboard.total_historical_positions') }}
                </span>
                <p class="text-xl sm:text-2xl font-bold font-mono tabular-nums text-slate-900 mt-2 tracking-tight">
                    {{ $summary['total_positions_count'] }}
                </p>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('dashboard.includes_zero_quantity') }}</p>
            </div>
        </div>

        @if(\App\Support\Currency::getUserCurrency() !== \App\Support\Currency::TWD)
            <!-- Estimated Currency Reference Rate Disclosure Banner -->
            <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-200/70 text-xs text-blue-900 flex items-center gap-2.5 shadow-2xs">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="leading-relaxed">{{ __('common.currency_rate_note', ['rate' => \App\Support\Currency::getRate(\App\Support\Currency::TWD, \App\Support\Currency::getUserCurrency()) ?? 'N/A', 'currency' => \App\Support\Currency::getUserCurrency()]) }}</span>
            </div>
        @endif

        <!-- 4. Middle Row: Monthly Realized P/L Chart & Current Month Activity Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Dashboard Lightweight Chart (2 cols) -->
            <div class="lg:col-span-2 p-5 rounded-xl bg-white shadow-card border border-slate-200/90 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('dashboard.monthly_realized_pl') }}</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ __('dashboard.monthly_realized_pl_desc') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200/70">
                            {{ __('dashboard.immutable_ledger') }}
                        </span>
                    </div>

                    <div class="mt-4">
                        @if ($realized_profit_chart['has_data'])
                            <div class="h-64 w-full relative">
                                <canvas id="dashboardMonthlyProfitChart"></canvas>
                            </div>
                        @else
                            <div class="h-64 flex flex-col items-center justify-center text-center p-6 bg-slate-50/70 rounded-xl border border-dashed border-slate-200">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-700">{{ __('dashboard.no_realized_pl_data') }}</p>
                                <p class="text-[11px] text-slate-400 mt-1 max-w-sm">
                                    {{ __('dashboard.no_realized_pl_data_desc') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="pt-3 mt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>{{ __('dashboard.excludes_unrealized') }}</span>
                    <a href="{{ route('analytics.index') }}" class="font-semibold text-blue-600 hover:text-blue-700 hover:underline transition-colors">
                        {{ __('dashboard.open_full_analytics') }}
                    </a>
                </div>
            </div>

            <!-- Right: Current Month Activity & Realized P/L Summary (1 col) -->
            <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('dashboard.recent_activity') }}</h3>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $current_month_activity['month_name'] }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ __('dashboard.current_month') }}
                        </span>
                    </div>

                    <!-- Month Stats List -->
                    <div class="space-y-3 mt-4">
                        <div class="flex items-center justify-between p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                            <div>
                                <span class="text-xs font-semibold text-slate-700">{{ __('dashboard.buy_orders') }}</span>
                                <p class="text-[11px] text-slate-400">{{ \App\Support\Currency::formatConverted($current_month_activity['buy_amount']) }} {{ __('dashboard.invested') }}</p>
                            </div>
                            <span class="text-base font-bold font-mono tabular-nums text-emerald-600">
                                {{ $current_month_activity['buy_count'] }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                            <div>
                                <span class="text-xs font-semibold text-slate-700">{{ __('dashboard.sell_orders') }}</span>
                                <p class="text-[11px] text-slate-400">{{ \App\Support\Currency::formatConverted($current_month_activity['sell_amount']) }} {{ __('dashboard.proceeds') }}</p>
                            </div>
                            <span class="text-base font-bold font-mono tabular-nums text-rose-600">
                                {{ $current_month_activity['sell_count'] }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between p-3.5 rounded-lg bg-slate-50/70 border border-slate-200/70">
                            <div>
                                <span class="text-xs font-semibold text-slate-700">{{ __('dashboard.realized_pl_this_month') }}</span>
                                <p class="text-[11px] text-slate-400">{{ __('dashboard.net_closed_gains_losses') }}</p>
                            </div>
                            @php
                                $isMonthPos = \App\Services\Financial\FinancialMath::greaterThan((string) $current_month_activity['realized_profit'], '0');
                                $isMonthNeg = \App\Services\Financial\FinancialMath::lessThan((string) $current_month_activity['realized_profit'], '0');
                            @endphp
                            <span class="text-sm font-bold font-mono tabular-nums {{ $isMonthPos ? 'text-emerald-600' : ($isMonthNeg ? 'text-rose-600' : 'text-slate-700') }}">
                                {{ \App\Support\Currency::formatConverted($current_month_activity['realized_profit'], decimals: 2, showPlusSign: true) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="pt-3 mt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ __('dashboard.all_time_closed_pl') }}</span>
                        <span class="font-bold font-mono tabular-nums {{ $isProfitPos ? 'text-emerald-600' : ($isProfitNeg ? 'text-rose-600' : 'text-slate-700') }}">
                            {{ \App\Support\Currency::formatConverted($summary['total_realized_profit'], decimals: 2, showPlusSign: true) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Current Holdings Table (Active Positions Only) -->
        <div class="rounded-xl bg-white shadow-card border border-slate-200/90 overflow-hidden">
            <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">{{ __('dashboard.current_holdings') }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $current_holdings->count() }} {{ __('dashboard.active_positions') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('dashboard.current_holdings_desc') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('portfolio.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        <span>{{ __('dashboard.view_full_portfolio') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">{{ __('dashboard.company') }}</th>
                            <th class="py-3 px-4">{{ __('dashboard.symbol') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.quantity') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.wac') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.inventory_cost_basis') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.realized_profit_loss') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.last_transaction') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($current_holdings as $position)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Company Name linking to Portfolio Position Detail -->
                                <td class="py-3.5 px-4 font-medium text-slate-800">
                                    <a href="{{ route('portfolio.positions.show', $position) }}" class="hover:text-blue-600 hover:underline transition-colors font-semibold">
                                        {{ $position->company->name ?? '—' }}
                                    </a>
                                </td>

                                <!-- Symbol linking to Stock Workspace -->
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    @if ($position->company)
                                        <a href="{{ route('stocks.show', $position->company) }}" class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                            {{ $position->company->symbol }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- Quantity -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ number_format((float) $position->quantity, 4) }}
                                </td>

                                <!-- Average / WAC -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ \App\Support\Currency::formatConverted($position->average_price, decimals: 4) }}
                                </td>

                                <!-- Inventory Cost Basis -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-bold">
                                    {{ \App\Support\Currency::formatConverted($position->inventory_amount, decimals: 2) }}
                                </td>

                                <!-- Realized P/L -->
                                @php
                                    $isPosProfitPos = \App\Services\Financial\FinancialMath::greaterThan((string) $position->realized_profit, '0');
                                    $isPosProfitNeg = \App\Services\Financial\FinancialMath::lessThan((string) $position->realized_profit, '0');
                                @endphp
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold {{ $isPosProfitPos ? 'text-emerald-600' : ($isPosProfitNeg ? 'text-rose-600' : 'text-slate-500') }}">
                                    {{ \App\Support\Currency::formatConverted($position->realized_profit, decimals: 2, showPlusSign: true) }}
                                </td>

                                <!-- Last Transaction Date -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-500 text-[11px] whitespace-nowrap">
                                    {{ $position->last_transaction_at ? $position->last_transaction_at->format('Y-m-d') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">{{ __('dashboard.no_active_holdings') }}</p>
                                    <p class="text-xs text-slate-400 mt-1 mb-4">{{ __('dashboard.no_active_holdings_desc') }}</p>
                                    <a href="{{ route('companies.index') }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ __('dashboard.browse_companies') }}</span>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 6. Recent Transactions Table (Latest 10 records) -->
        <div class="rounded-xl bg-white shadow-card border border-slate-200/90 overflow-hidden">
            <div class="p-5 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900">{{ __('dashboard.recent_transactions') }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200/70">
                            {{ __('dashboard.latest_of', ['count' => $recent_transactions->count(), 'total' => 10]) }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('dashboard.recent_transactions_desc') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('companies.index') }}"
                       class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        <span>{{ __('dashboard.trade_stocks') }}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">{{ __('dashboard.date') }}</th>
                            <th class="py-3 px-4">{{ __('dashboard.company') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('dashboard.type') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.quantity') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.price') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.amount') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('dashboard.realized_profit_loss') }}</th>
                            <th class="py-3 px-4">{{ __('dashboard.notes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recent_transactions as $tx)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- Date -->
                                <td class="py-3.5 px-4 text-slate-600 font-mono tabular-nums text-[11px] whitespace-nowrap">
                                    {{ $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '—' }}
                                </td>

                                <!-- Company linking to workspace -->
                                <td class="py-3.5 px-4 font-medium text-slate-800">
                                    @if ($tx->company)
                                        <div class="flex items-center gap-2">
                                            <a href="{{ route('stocks.show', $tx->company) }}" class="inline-flex items-center px-1.5 py-0.5 rounded-md font-mono font-bold text-[11px] bg-slate-100 text-slate-800 border border-slate-200 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                                {{ $tx->company->symbol }}
                                            </a>
                                            <a href="{{ route('stocks.show', $tx->company) }}" class="hover:text-blue-600 hover:underline transition-colors truncate max-w-[160px] sm:max-w-xs font-semibold">
                                                {{ $tx->company->name }}
                                            </a>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>

                                <!-- BUY / SELL Type -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    @if ($tx->type->isBuy())
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                                            {{ __('transactions.buy_action') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200/80">
                                            {{ __('transactions.sell_action') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Quantity -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ number_format((float) $tx->quantity, 4) }}
                                </td>

                                <!-- Price -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-medium">
                                    {{ \App\Support\Currency::formatConverted($tx->price, decimals: 4) }}
                                </td>

                                <!-- Amount -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums text-slate-900 font-semibold">
                                    {{ \App\Support\Currency::formatConverted($tx->amount, decimals: 2) }}
                                </td>

                                <!-- Realized Profit for SELL / Dash for BUY -->
                                <td class="py-3.5 px-4 text-right font-mono tabular-nums font-semibold">
                                    @if ($tx->type->isSell())
                                        @php
                                            $isTxProfitPos = \App\Services\Financial\FinancialMath::greaterThan((string) $tx->realized_profit, '0');
                                            $isTxProfitNeg = \App\Services\Financial\FinancialMath::lessThan((string) $tx->realized_profit, '0');
                                        @endphp
                                        <span class="{{ $isTxProfitPos ? 'text-emerald-600' : ($isTxProfitNeg ? 'text-rose-600' : 'text-slate-500') }}">
                                            {{ \App\Support\Currency::formatConverted($tx->realized_profit, decimals: 2, showPlusSign: true) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- Notes -->
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate text-[11px]">
                                    {{ $tx->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-500">
                                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">{{ __('dashboard.no_recent_transactions') }}</p>
                                    <p class="text-xs text-slate-400 mt-1 mb-4">{{ __('dashboard.no_recent_transactions_desc') }}</p>
                                    <a href="{{ route('companies.index') }}"
                                       class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ __('dashboard.first_buy_transaction') }}</span>
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

            const gridColor = 'rgba(226, 232, 240, 0.8)';
            const textColor = '#64748b';

            new Chart(canvas, {
                type: 'bar',
                data: {
                    labels: chartPayload.labels,
                    datasets: chartPayload.datasets.map(ds => ({
                        ...ds,
                        borderRadius: 6,
                        maxBarThickness: 44,
                        categoryPercentage: 0.6,
                        barPercentage: 0.8,
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            top: 12,
                            right: 12,
                            bottom: 4,
                            left: 4,
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 12, weight: '600' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            cornerRadius: 8,
                            callbacks: {
                                label: function (ctx) {
                                    const val = ctx.parsed.y;
                                    const symbol = @json(\App\Support\Currency::symbol());
                                    const formatted = new Intl.NumberFormat('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format(Math.abs(val));
                                    return ' ' + (val < 0 ? '-' : (val > 0 ? '+' : '')) + symbol + formatted;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: textColor,
                                font: { size: 11, family: 'Inter, system-ui, sans-serif' }
                            }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                font: { size: 11, family: 'Inter, system-ui, sans-serif' },
                                callback: function (val) {
                                    const symbol = @json(\App\Support\Currency::symbol());
                                    const sign = val < 0 ? '-' : (val > 0 ? '+' : '');
                                    const absVal = Math.abs(val);

                                    if (absVal === 0) {
                                        return symbol + '0';
                                    }

                                    if (absVal < 1000) {
                                        const formatted = new Intl.NumberFormat('en-US', {
                                            minimumFractionDigits: absVal < 10 ? 2 : 0,
                                            maximumFractionDigits: 2
                                        }).format(absVal);
                                        return sign + symbol + formatted;
                                    }

                                    const formatted = new Intl.NumberFormat('en-US', {
                                        notation: 'compact',
                                        maximumFractionDigits: 1
                                    }).format(absVal);
                                    return sign + symbol + formatted;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>
