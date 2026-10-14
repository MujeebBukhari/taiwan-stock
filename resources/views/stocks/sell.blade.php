<x-app-layout>
    <x-slot name="header">
        {{ __('stocks.record_sell_title', ['symbol' => $company->symbol]) }}
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Company & Current Position Context Header -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded font-mono text-sm font-bold bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700">
                        {{ $company->symbol }}
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">{{ $company->name }}</h2>
                        <span class="text-xs text-slate-400 font-medium">{{ $company->exchange }} &bull; {{ __('stocks.currency') }}: {{ $company->currency }}</span>
                    </div>
                </div>

                <a href="{{ route('stocks.show', $company) }}"
                   class="text-xs font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-slate-200 transition-colors">
                    &larr; {{ __('stocks.back_to_workspace') }}
                </a>
            </div>

            <!-- Current Position Stats Snapshot -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4 pt-1">
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('stocks.available_shares') }}</span>
                    <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200">
                        {{ number_format((float) ($position?->quantity ?? 0), 4) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('stocks.weighted_avg_price') }}</span>
                    <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200">
                        {{ $company->currency_symbol }} {{ number_format((float) ($position?->average_price ?? 0), 4) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('stocks.inventory_amount') }}</span>
                    <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200">
                        {{ $company->currency_symbol }} {{ number_format((float) ($position?->inventory_amount ?? 0), 2) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('stocks.realized_profit') }}</span>
                    @php
                        $realizedProfit = (float) ($position?->realized_profit ?? 0);
                    @endphp
                    <span class="text-sm font-bold font-mono {{ $realizedProfit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($realizedProfit < 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-800 dark:text-slate-200') }}">
                        {{ $company->currency_symbol }} {{ number_format($realizedProfit, 2) }}
                    </span>
                </div>
            </div>
        </div>

        @php
            $availableQty = (float) ($position?->quantity ?? 0);
            $hasShares = $availableQty > 0;
            $currentWac = (float) ($position?->average_price ?? 0);
        @endphp

        @if (! $hasShares)
            <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 text-xs text-amber-800 dark:text-amber-200">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <p class="font-bold">{{ __('stocks.no_shares_to_sell') }}</p>
                        <p class="text-[11px] text-amber-700 dark:text-amber-300 mt-0.5">
                            {{ __('stocks.no_shares_desc', ['symbol' => $company->symbol]) }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- SELL Transaction Form -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="pb-4 mb-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('stocks.execute_sell_order') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('stocks.sell_atomic_desc', ['currency' => $company->currency, 'wac' => number_format($currentWac, 4)]) }}
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-700 dark:text-red-300">
                    <p class="font-bold mb-1">{{ __('common.error') }}:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('stocks.sell.store', $company) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">

                <!-- Quantity & Price Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="quantity" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                {{ __('stocks.sell_quantity') }} <span class="text-red-500">*</span>
                            </label>
                            @if ($hasShares)
                                <button type="button"
                                        id="sell-max-btn"
                                        class="text-[11px] font-semibold text-rose-600 dark:text-rose-400 hover:underline">
                                    {{ __('stocks.sell_all', ['qty' => number_format($availableQty, 4)]) }}
                                </button>
                            @endif
                        </div>
                        <input type="number"
                               id="quantity"
                               name="quantity"
                               step="0.00000001"
                               min="0.00000001"
                               max="{{ $hasShares ? $availableQty : '' }}"
                               value="{{ old('quantity') }}"
                               placeholder="e.g. 50"
                               required
                               {{ ! $hasShares ? 'disabled' : '' }}
                               class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed">
                        <p class="text-[11px] text-slate-400 mt-1">
                            {{ __('stocks.available_to_sell') }} <span class="font-mono font-semibold">{{ number_format($availableQty, 4) }}</span> {{ __('transactions.shares') }}.
                        </p>
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('stocks.unit_sell_price', ['currency' => $company->currency]) }} <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               id="price"
                               name="price"
                               step="0.00000001"
                               min="0.00000001"
                               value="{{ old('price', $company->last_price ?? ($position && (float) $position->average_price > 0 ? (float) $position->average_price : '')) }}"
                               placeholder="e.g. 70.00"
                               required
                               {{ ! $hasShares ? 'disabled' : '' }}
                               class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.exec_selling_price_desc') }}</p>
                    </div>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('stocks.transaction_date') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           id="transaction_date"
                           name="transaction_date"
                           value="{{ old('transaction_date', date('Y-m-d')) }}"
                           required
                           {{ ! $hasShares ? 'disabled' : '' }}
                           class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed">
                </div>

                <!-- Non-authoritative Estimation Card -->
                <div class="p-3.5 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">{{ __('stocks.estimated_gross_revenue') }}</span>
                        <span id="preview-amount" class="font-bold font-mono text-slate-900 dark:text-white">
                            {{ $company->currency }} 0.00
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 dark:text-slate-300 font-medium">{{ __('stocks.cost_sold_inventory') }}</span>
                        <span id="preview-cost" class="font-bold font-mono text-slate-600 dark:text-slate-400">
                            {{ $company->currency }} 0.00
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs pt-1.5 border-t border-slate-200 dark:border-slate-700">
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ __('stocks.estimated_realized_pl') }}</span>
                        <span id="preview-profit" class="font-bold font-mono text-slate-900 dark:text-white">
                            {{ $company->currency }} 0.00
                        </span>
                    </div>
                    <span class="block text-[11px] text-slate-400 pt-1">
                        {{ __('stocks.preview_estimate_sell') }}
                    </span>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('stocks.transaction_notes') }}
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              placeholder="e.g. Sold partial holding through broker."
                              {{ ! $hasShares ? 'disabled' : '' }}
                              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-rose-500 focus:border-transparent disabled:opacity-60 disabled:cursor-not-allowed">{{ old('notes') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('stocks.show', $company) }}"
                       class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg border border-slate-300 dark:border-slate-600 transition-colors">
                        {{ __('common.cancel') }}
                    </a>

                    <button type="submit"
                            {{ ! $hasShares ? 'disabled' : '' }}
                            class="inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-500 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-rose-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                        <span>{{ __('stocks.confirm_sell_order') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Client-side non-authoritative preview calculation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qtyInput = document.getElementById('quantity');
            const priceInput = document.getElementById('price');
            const previewAmountEl = document.getElementById('preview-amount');
            const previewCostEl = document.getElementById('preview-cost');
            const previewProfitEl = document.getElementById('preview-profit');
            const sellMaxBtn = document.getElementById('sell-max-btn');
            const currency = @json($company->currency);
            const currentWac = {{ $currentWac }};
            const availableQty = {{ $availableQty }};

            function updatePreview() {
                if (!qtyInput || !priceInput) return;
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const revenue = qty * price;
                const cost = qty * currentWac;
                const profit = revenue - cost;

                previewAmountEl.textContent = `${currency} ${revenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                previewCostEl.textContent = `${currency} ${cost.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

                const formattedProfit = profit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (profit > 0) {
                    previewProfitEl.textContent = `+${currency} ${formattedProfit}`;
                    previewProfitEl.className = 'font-bold font-mono text-emerald-600 dark:text-emerald-400';
                } else if (profit < 0) {
                    previewProfitEl.textContent = `${currency} ${formattedProfit}`;
                    previewProfitEl.className = 'font-bold font-mono text-rose-600 dark:text-rose-400';
                } else {
                    previewProfitEl.textContent = `${currency} ${formattedProfit}`;
                    previewProfitEl.className = 'font-bold font-mono text-slate-900 dark:text-white';
                }
            }

            if (qtyInput && priceInput) {
                qtyInput.addEventListener('input', updatePreview);
                priceInput.addEventListener('input', updatePreview);
                updatePreview();
            }

            if (sellMaxBtn && qtyInput) {
                sellMaxBtn.addEventListener('click', function () {
                    qtyInput.value = availableQty;
                    updatePreview();
                });
            }
        });
    </script>
</x-app-layout>
