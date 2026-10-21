<x-app-layout>
    <x-slot name="header">
        {{ __('portfolio.title') }} &bull; {{ __('portfolio.holdings') }} &amp; {{ __('portfolio.cost_basis') }}
    </x-slot>

    <div class="space-y-6">
        <!-- Dashboard Summary Header -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ __('portfolio.overview') }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        {{ __('portfolio.overview_desc') }}
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('companies.index') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('portfolio.trade_stocks') }}</span>
                    </a>
                </div>
            </div>

            <!-- Summary Cards Grid (5 cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                <!-- 1. Total Inventory Cost -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('portfolio.total_inventory_cost') }}
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ __('portfolio.cost_basis') }}
                        </span>
                    </div>
                    <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                        {{ \App\Support\Currency::formatConverted($summary['total_inventory_cost'], decimals: 2) }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">
                        {{ __('portfolio.total_acq_cost') }}
                        @if(\App\Support\Currency::getUserCurrency() !== \App\Support\Currency::TWD)
                            <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-sans block sm:inline">({{ __('common.currency_rate_note', ['rate' => \App\Support\Currency::getRate(\App\Support\Currency::TWD, \App\Support\Currency::getUserCurrency()) ?? 'N/A', 'currency' => \App\Support\Currency::getUserCurrency()]) }})</span>
                        @endif
                    </p>
                </div>

                <!-- 2. Total Realized Profit -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            {{ __('portfolio.realized_profit') }}
                        </span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                            {{ __('portfolio.closed_pl') }}
                        </span>
                    </div>
                    @php
                        $isPortProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $summary['total_realized_profit'], '0');
                        $isPortProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $summary['total_realized_profit'], '0');
                    @endphp
                    <p class="text-xl font-bold font-mono mt-2 {{ $isPortProfPos ? 'text-emerald-600 dark:text-emerald-400' : ($isPortProfNeg ? 'text-rose-600 dark:text-rose-400' : 'text-slate-900 dark:text-white') }}">
                        {{ \App\Support\Currency::formatConverted($summary['total_realized_profit'], decimals: 2, showPlusSign: true) }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.cumulative_closed_profit') }}</p>
                </div>

                <!-- 3. Total Quantity -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('portfolio.total_quantity') }}
                    </span>
                    <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                        {{ number_format((float) $summary['total_quantity'], 4) }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.aggregate_shares') }}</p>
                </div>

                <!-- 4. Active Positions -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('portfolio.active_positions') }}
                    </span>
                    <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                        {{ $summary['active_positions_count'] }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.holdings_gt_zero') }}</p>
                </div>

                <!-- 5. Companies Held -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        {{ __('portfolio.companies_held') }}
                    </span>
                    <p class="text-xl font-bold font-mono text-slate-900 dark:text-white mt-2">
                        {{ $summary['companies_held_count'] }}
                    </p>
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('portfolio.distinct_stocks') }}</p>
                </div>
            </div>
        </div>

        @if ($summary['total_positions_count'] === 0)
            <!-- Full Empty State -->
            <div class="p-12 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center text-emerald-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('portfolio.no_positions_yet') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                    {{ __('portfolio.no_positions_desc') }}
                </p>
                <div class="mt-6">
                    <a href="{{ route('companies.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>{{ __('portfolio.browse_companies') }}</span>
                    </a>
                </div>
            </div>
        @else
            @if ($summary['active_positions_count'] === 0 && $summary['total_positions_count'] > 0)
                <!-- Informational banner for zero-quantity holdings -->
                <div class="p-4 rounded-xl bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs text-slate-600 dark:text-slate-300">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>{{ __('portfolio.no_active_holdings_banner') }}</p>
                    </div>
                </div>
            @endif

            <!-- Positions Table & Filter Section -->
            <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <!-- Filter Bar -->
                <div class="p-5 border-b border-slate-200 dark:border-slate-700">
                    <form method="GET" action="{{ route('portfolio.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                        <!-- Search Input -->
                        <div class="lg:col-span-2">
                            <label for="search" class="sr-only">{{ __('companies.search') }}</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input type="text"
                                       id="search"
                                       name="search"
                                       value="{{ $filters['search'] ?? '' }}"
                                       placeholder="{{ __('companies.search_placeholder') }}"
                                       class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                            </div>
                        </div>

                        <!-- Exchange Dropdown -->
                        <div>
                            <label for="exchange" class="sr-only">{{ __('companies.exchange') }}</label>
                            <select id="exchange"
                                    name="exchange"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="">{{ __('companies.all_exchanges') }}</option>
                                @foreach ($filterOptions['exchanges'] as $exchangeOption)
                                    <option value="{{ $exchangeOption }}" {{ ($filters['exchange'] ?? '') === $exchangeOption ? 'selected' : '' }}>
                                        {{ $exchangeOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sector Dropdown -->
                        <div>
                            <label for="sector" class="sr-only">{{ __('companies.sector') }}</label>
                            <select id="sector"
                                    name="sector"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="">{{ __('companies.all_sectors') }}</option>
                                @foreach ($filterOptions['sectors'] as $sectorOption)
                                    <option value="{{ $sectorOption }}" {{ ($filters['sector'] ?? '') === $sectorOption ? 'selected' : '' }}>
                                        {{ $sectorOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Dropdown & Submit -->
                        <div class="flex items-center gap-2">
                            <select id="status"
                                    name="status"
                                    class="flex-1 px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="all" {{ ($filters['status'] ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('portfolio.all_status') }}</option>
                                <option value="holding" {{ ($filters['status'] ?? '') === 'holding' ? 'selected' : '' }}>{{ __('portfolio.status_holding') }}</option>
                                <option value="zero" {{ ($filters['status'] ?? '') === 'zero' ? 'selected' : '' }}>{{ __('portfolio.status_zero') }}</option>
                            </select>

                            <button type="submit"
                                    class="px-3 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-colors">
                                {{ __('common.filter') }}
                            </button>

                            @if (!empty($filters['search']) || !empty($filters['exchange']) || !empty($filters['sector']) || (($filters['status'] ?? 'all') !== 'all'))
                                <a href="{{ route('portfolio.index') }}"
                                   title="{{ __('common.reset') }}"
                                   class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <!-- Table Header Info -->
                <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 bg-slate-50/50 dark:bg-slate-900/30">
                    <span class="font-semibold text-slate-700 dark:text-slate-300">{{ __('portfolio.positions_list') }}</span>
                    <span class="font-mono">{{ __('portfolio.showing_positions', ['count' => $positions->total()]) }}</span>
                </div>

                <!-- Positions Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4">{{ __('portfolio.company') }}</th>
                                <th class="py-3 px-4">{{ __('portfolio.exchange') }}</th>
                                <th class="py-3 px-4">{{ __('portfolio.sector') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('portfolio.current_quantity') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('portfolio.average_wac') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('portfolio.inventory_cost') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('portfolio.realized_profit') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('portfolio.last_transaction') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('common.status') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('common.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @forelse ($positions as $position)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                    <!-- Company -->
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-2.5">
                                            <span class="px-2 py-0.5 rounded font-mono text-xs font-bold bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700">
                                                {{ $position->company->symbol }}
                                            </span>
                                            <div class="min-w-0">
                                                <a href="{{ route('stocks.show', $position->company) }}" class="font-semibold text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 truncate block">
                                                    {{ $position->company->name }}
                                                </a>
                                                <span class="text-[11px] text-slate-400 font-mono">{{ $position->company->currency }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Exchange -->
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">
                                        {{ $position->company->exchange }}
                                    </td>

                                    <!-- Sector -->
                                    <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                        {{ $position->company->sector ?? __('stocks.unspecified') }}
                                    </td>

                                    <!-- Quantity -->
                                    <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                        {{ number_format((float) $position->quantity, 4) }}
                                    </td>

                                    <!-- Average (WAC) -->
                                    <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-medium">
                                        {{ \App\Support\Currency::formatConverted($position->average_price, decimals: 4) }}
                                    </td>

                                    <!-- Inventory Cost -->
                                    <td class="py-3 px-4 text-right font-mono text-slate-900 dark:text-slate-100 font-semibold">
                                        {{ \App\Support\Currency::formatConverted($position->inventory_amount, decimals: 2) }}
                                    </td>

                                    <!-- Realized Profit -->
                                    @php
                                        $isPosProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $position->realized_profit, '0');
                                        $isPosProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $position->realized_profit, '0');
                                    @endphp
                                    <td class="py-3 px-4 text-right font-mono font-semibold {{ $isPosProfPos ? 'text-emerald-600 dark:text-emerald-400' : ($isPosProfNeg ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') }}">
                                        {{ \App\Support\Currency::formatConverted($position->realized_profit, decimals: 2, showPlusSign: true) }}
                                    </td>

                                    <!-- Last Transaction -->
                                    <td class="py-3 px-4 text-center font-mono text-[11px] text-slate-500 dark:text-slate-400 whitespace-nowrap">
                                        {{ $position->last_transaction_at ? $position->last_transaction_at->format('Y-m-d') : '—' }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3 px-4 text-center">
                                        @if ($position->isHolding())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                {{ __('portfolio.holding') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                {{ __('portfolio.zero') }}
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Action -->
                                    <td class="py-3 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2.5">
                                            <a href="{{ route('portfolio.positions.show', $position) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 transition-colors">
                                                <span>{{ __('portfolio.details') }}</span>
                                            </a>
                                            <a href="{{ route('stocks.show', $position->company) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold text-emerald-700 dark:text-emerald-300 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900/60 transition-colors">
                                                <span>{{ __('portfolio.workspace') }}</span>
                                                <span>&rarr;</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ __('portfolio.no_matching_positions') }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ __('portfolio.try_clearing_filters') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Container -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40">
                    {{ $positions->links() }}
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
