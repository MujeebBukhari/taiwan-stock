<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ __('stocks.record_buy_transaction') }} &bull; {{ $company->symbol }}
            </h1>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline-flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                {{ $company->name }} ({{ $company->currency }})
            </span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Company & Current Position Context Header -->
        <div class="p-6 rounded-xl bg-white shadow-card border border-slate-200/90 transition-all duration-150">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <span class="px-2.5 py-1 rounded-md font-mono text-sm font-bold bg-slate-100 text-slate-900 border border-slate-200">
                        {{ $company->symbol }}
                    </span>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">{{ $company->name }}</h2>
                        <span class="text-xs text-slate-400 font-medium">{{ $company->exchange }} &bull; {{ __('stocks.currency') }}: {{ $company->currency }}</span>
                    </div>
                </div>

                <a href="{{ route('stocks.show', $company) }}"
                   class="inline-flex items-center gap-1 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('stocks.back_to_workspace') }}</span>
                </a>
            </div>

            <!-- Current Position Stats Snapshot -->
            <div class="grid grid-cols-3 gap-4 mt-4 pt-1">
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('portfolio.current_position') }}</span>
                    <span class="text-sm font-bold font-mono tabular-nums text-slate-800 mt-0.5 block">
                        {{ number_format((float) ($position?->quantity ?? 0), 4) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('stocks.weighted_avg_price') }}</span>
                    <span class="text-sm font-bold font-mono tabular-nums text-slate-800 mt-0.5 block">
                        {{ \App\Support\Currency::formatConverted($position?->average_price ?? 0, decimals: 4) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('stocks.inventory_amount') }}</span>
                    <span class="text-sm font-bold font-mono tabular-nums text-slate-800 mt-0.5 block">
                        {{ \App\Support\Currency::formatConverted($position?->inventory_amount ?? 0) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- BUY Transaction Form -->
        <div class="p-6 sm:p-8 rounded-xl bg-white shadow-card border border-slate-200/90">
            <div class="pb-4 mb-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">{{ __('stocks.execute_buy_order') }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('stocks.wac_atomic_desc') }}
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-xs text-red-700">
                    <p class="font-bold mb-1">{{ __('common.error') }}:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('stocks.buy.store', $company) }}" class="space-y-4">
                @csrf
                <input type="hidden" name="company_id" value="{{ $company->id }}">

                <!-- Quantity & Price Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ __('stocks.buy_quantity') }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="number"
                               id="quantity"
                               name="quantity"
                               step="0.00000001"
                               min="0.00000001"
                               value="{{ old('quantity') }}"
                               placeholder="e.g. 100"
                               required
                               class="w-full px-3.5 py-2 text-xs font-mono tabular-nums rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.buy_shares_desc') }}</p>
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ __('stocks.unit_buy_price', ['currency' => $company->currency]) }} <span class="text-rose-500">*</span>
                        </label>
                        <input type="number"
                               id="price"
                               name="price"
                               step="0.00000001"
                               min="0.00000001"
                               value="{{ old('price', $company->last_price ?? ($position && (float) $position->average_price > 0 ? (float) $position->average_price : '')) }}"
                               placeholder="e.g. 50.00"
                               required
                               class="w-full px-3.5 py-2 text-xs font-mono tabular-nums rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('stocks.exec_purchase_price_desc') }}</p>
                    </div>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('stocks.transaction_date') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date"
                           id="transaction_date"
                           name="transaction_date"
                           value="{{ old('transaction_date', date('Y-m-d')) }}"
                           required
                           class="w-full px-3.5 py-2 text-xs font-mono rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                </div>

                <!-- Non-authoritative Estimation Card -->
                <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-700">{{ __('stocks.estimated_purchase_amount') }}</span>
                        <div class="text-right">
                            <span id="preview-amount" class="text-base font-bold font-mono tabular-nums text-blue-600">
                                {{ $company->currency }} 0.00
                            </span>
                            @if(\App\Support\Currency::getUserCurrency() === 'USD')
                                <span id="preview-amount-usd" class="block text-xs font-mono tabular-nums text-slate-500 mt-0.5">
                                    ≈ US$ 0.00
                                </span>
                            @endif
                        </div>
                    </div>
                    <span class="block text-[11px] text-slate-400 mt-1">
                        {{ __('stocks.preview_estimate_buy') }} ({{ __('common.accounting') }}: {{ $company->currency }})
                    </span>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('stocks.transaction_notes') }}
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              placeholder="{{ __('stocks.notes_placeholder_buy') }}"
                              class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">{{ old('notes') }}</textarea>
                </div>

                <!-- Action Buttons -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                    <a href="{{ route('stocks.show', $company) }}"
                       class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-lg border border-slate-200 shadow-xs hover:border-slate-300 transition-colors">
                        {{ __('common.cancel') }}
                    </a>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('stocks.confirm_buy_order') }}</span>
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
            const previewEl = document.getElementById('preview-amount');
            const previewUsdEl = document.getElementById('preview-amount-usd');
            const currency = @json($company->currency);
            const rate = {{ \App\Support\Currency::getRate('TWD', 'USD') ?? '0.03125' }};

            function updatePreview() {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;
                previewEl.textContent = `${currency} ${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                if (previewUsdEl) {
                    const totalUsd = total * rate;
                    previewUsdEl.textContent = `≈ US$ ${totalUsd.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                }
            }

            qtyInput.addEventListener('input', updatePreview);
            priceInput.addEventListener('input', updatePreview);
            updatePreview();
        });
    </script>
</x-app-layout>
