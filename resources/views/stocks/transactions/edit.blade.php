<x-app-layout>
    <x-slot name="header">
        {{ __('transactions.edit_transaction_title', ['id' => $transaction->id, 'symbol' => $company->symbol]) }}
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <!-- Company & Transaction Context Header -->
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

            <!-- Transaction Snapshot Cards -->
            <div class="grid grid-cols-3 gap-4 mt-4 pt-1">
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('transactions.type') }}</span>
                    <div class="mt-1">
                        @if ($transaction->type->isBuy())
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                {{ __('transactions.buy_action') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                {{ __('transactions.sell_action') }}
                            </span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">{{ __('transactions.total_amount') }}</span>
                    <span class="text-sm font-bold font-mono text-slate-800 dark:text-slate-200">
                        {{ $company->currency_symbol }} {{ number_format((float) $transaction->amount, 2) }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-semibold uppercase text-slate-400">
                        {{ $transaction->type->isBuy() ? __('transactions.cost') : __('transactions.realized_profit') }}
                    </span>
                    <span class="text-sm font-bold font-mono {{ $transaction->type->isSell() && (float) $transaction->realized_profit > 0 ? 'text-emerald-600 dark:text-emerald-400' : ($transaction->type->isSell() && (float) $transaction->realized_profit < 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-200') }}">
                        @if ($transaction->type->isBuy())
                            {{ $company->currency_symbol }} {{ number_format((float) $transaction->cost, 2) }}
                        @else
                            {{ (float) $transaction->realized_profit > 0 ? '+' : '' }}{{ $company->currency_symbol }} {{ number_format((float) $transaction->realized_profit, 2) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Recalculation Notice Banner -->
        <div class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">{{ __('transactions.recalculation_notice') }}</h4>
                <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5 leading-relaxed">
                    {{ __('transactions.recalculation_notice_desc') }}
                </p>
            </div>
        </div>

        <!-- Edit Transaction Form -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="pb-4 mb-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ __('transactions.edit_transaction') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('transactions.edit_transaction_desc') }}
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

            <form method="POST" action="{{ route('stocks.transactions.update', [$company, $transaction]) }}" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Quantity & Price Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="quantity" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('transactions.quantity') }} ({{ __('transactions.shares') }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               step="any"
                               min="0.0001"
                               id="quantity"
                               name="quantity"
                               value="{{ old('quantity', (float) $transaction->quantity) }}"
                               required
                               placeholder="e.g. 100"
                               class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="price" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            {{ __('transactions.unit_price') }} ({{ $company->currency }}) <span class="text-red-500">*</span>
                        </label>
                        <input type="number"
                               step="any"
                               min="0.0001"
                               id="price"
                               name="price"
                               value="{{ old('price', (float) $transaction->price) }}"
                               required
                               placeholder="e.g. 150.50"
                               class="w-full px-3 py-2 text-xs font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('transactions.transaction_date') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                           id="transaction_date"
                           name="transaction_date"
                           value="{{ old('transaction_date', $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : date('Y-m-d')) }}"
                           required
                           class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        {{ __('transactions.notes') }}
                    </label>
                    <textarea id="notes"
                              name="notes"
                              rows="3"
                              placeholder="{{ __('stocks.optional_notes_placeholder') }}"
                              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">{{ old('notes', $transaction->notes) }}</textarea>
                </div>

                <!-- Live Estimated Value Preview -->
                <div class="p-3 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                        {{ __('transactions.execution_preview') }} ({{ __('transactions.total_amount') }}):
                    </span>
                    <span id="preview-amount" class="text-sm font-mono font-bold text-slate-900 dark:text-white">
                        {{ $company->currency_symbol }} 0.00
                    </span>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-4 flex items-center justify-between border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('stocks.show', $company) }}"
                       class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg border border-slate-300 dark:border-slate-600 transition-colors">
                        {{ __('common.cancel') }}
                    </a>

                    <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        {{ __('transactions.update_and_recalculate') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone: Delete Transaction -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-red-200 dark:border-red-900/50">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-bold text-red-600 dark:text-red-400">{{ __('transactions.delete_transaction') }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ __('transactions.delete_confirm') }}
                    </p>
                </div>

                <form method="POST" action="{{ route('stocks.transactions.destroy', [$company, $transaction]) }}" onsubmit="return confirm('{{ __('transactions.delete_confirm') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-500 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
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
