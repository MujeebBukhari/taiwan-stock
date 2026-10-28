<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ __('transactions.edit_transaction_title', ['id' => $transaction->id, 'symbol' => $company->symbol]) }}
            </h1>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline-flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                {{ $company->name }} ({{ $company->currency }})
            </span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Company & Transaction Context Header -->
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

            <!-- Transaction Snapshot Cards -->
            <div class="grid grid-cols-3 gap-4 mt-4 pt-1">
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('transactions.type') }}</span>
                    <div class="mt-1">
                        @if ($transaction->type->isBuy())
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ __('transactions.buy_action') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                {{ __('transactions.sell_action') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('transactions.total_amount') }}</span>
                    <span class="text-sm font-bold font-mono tabular-nums text-slate-800 mt-1 block">
                        {{ \App\Support\Currency::formatConverted($transaction->amount) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">
                        {{ $transaction->type->isBuy() ? __('transactions.cost') : __('transactions.realized_profit') }}
                    </span>
                    @php
                        $isEditProfPos = \App\Services\Financial\FinancialMath::greaterThan((string) $transaction->realized_profit, '0');
                        $isEditProfNeg = \App\Services\Financial\FinancialMath::lessThan((string) $transaction->realized_profit, '0');
                    @endphp
                    <span class="text-sm font-bold font-mono tabular-nums mt-1 block {{ $transaction->type->isSell() && $isEditProfPos ? 'text-emerald-600' : ($transaction->type->isSell() && $isEditProfNeg ? 'text-rose-600' : 'text-slate-800') }}">
                        @if ($transaction->type->isBuy())
                            {{ \App\Support\Currency::formatConverted($transaction->cost) }}
                        @else
                            {{ \App\Support\Currency::formatConverted($transaction->realized_profit, showPlusSign: true) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Recalculation Notice Banner -->
        <div class="p-4 rounded-xl bg-amber-50/80 border border-amber-200/80 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h4 class="text-xs font-bold text-amber-900">{{ __('transactions.recalculation_notice') }}</h4>
                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                    {{ __('transactions.recalculation_notice_desc') }}
                </p>
            </div>
        </div>

        <!-- Edit Transaction Form -->
        <div class="p-6 sm:p-8 rounded-xl bg-white shadow-card border border-slate-200/90">
            <div class="pb-4 mb-6 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">{{ __('transactions.edit_transaction') }}</h3>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('transactions.edit_transaction_desc') }}
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

            <form method="POST" action="{{ route('stocks.transactions.update', [$company, $transaction]) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Quantity & Price Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ __('transactions.quantity') }} ({{ __('transactions.shares') }}) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number"
                               step="any"
                               min="0.0001"
                               id="quantity"
                               name="quantity"
                               value="{{ old('quantity', (float) $transaction->quantity) }}"
                               required
                               placeholder="e.g. 100"
                               class="w-full px-3.5 py-2 text-xs font-mono tabular-nums rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ __('transactions.unit_price') }} ({{ $company->currency }}) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number"
                               step="any"
                               min="0.0001"
                               id="price"
                               name="price"
                               value="{{ old('price', (float) $transaction->price) }}"
                               required
                               placeholder="e.g. 150.50"
                               class="w-full px-3.5 py-2 text-xs font-mono tabular-nums rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('transactions.transaction_date') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date"
                           id="transaction_date"
                           name="transaction_date"
                           value="{{ old('transaction_date', $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d')) }}"
                           required
                           class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('transactions.notes') }}
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              placeholder="{{ __('stocks.optional_notes_placeholder') }}"
                              class="w-full px-3.5 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">{{ old('notes', $transaction->notes) }}</textarea>
                </div>

                <!-- Live Estimated Value Preview -->
                <div class="p-3.5 rounded-xl bg-slate-50/80 border border-slate-200/80 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-700">
                        {{ __('transactions.execution_preview') }} ({{ __('transactions.total_amount') }}):
                    </span>
                    <span id="preview-amount" class="text-sm font-mono tabular-nums font-bold text-slate-900">
                        {{ $company->currency_symbol }} 0.00
                    </span>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-4 flex items-center justify-between border-t border-slate-100">
                    <a href="{{ route('stocks.show', $company) }}"
                       class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-lg border border-slate-200 shadow-xs hover:border-slate-300 transition-colors">
                        {{ __('common.cancel') }}
                    </a>

                    <button type="submit"
                            class="px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 rounded-lg shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        {{ __('transactions.update_and_recalculate') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone: Delete Transaction -->
        <div class="p-6 rounded-xl bg-white shadow-card border border-rose-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h4 class="text-sm font-bold text-rose-600">{{ __('transactions.delete_transaction') }}</h4>
                    <p class="text-xs text-slate-500 mt-1">
                        {{ __('transactions.delete_confirm') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('stocks.transactions.destroy', [$company, $transaction]) }}" onsubmit="return confirm('{{ __('transactions.delete_confirm') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-lg shadow-sm transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-rose-500/30">
                        {{ __('transactions.delete_transaction') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Client-side preview calculation script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const qtyInput = document.getElementById('quantity');
            const priceInput = document.getElementById('price');
            const previewEl = document.getElementById('preview-amount');
            const currencySymbol = @json($company->currency_symbol);

            function updatePreview() {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                const total = qty * price;
                previewEl.textContent = `${currencySymbol} ${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }

            qtyInput.addEventListener('input', updatePreview);
            priceInput.addEventListener('input', updatePreview);
            updatePreview();
        });
    </script>
</x-app-layout>
