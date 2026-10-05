<x-app-layout>
    <x-slot name="header">
        Add New Company
    </x-slot>

    <div class="max-w-2xl mx-auto space-y-6">
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="pb-4 mb-6 border-b border-slate-200 dark:border-slate-700">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Create Company Master Record</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Define a new listed company for transaction recording and portfolio tracking.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-900/60 text-xs text-red-700 dark:text-red-300">
                    <p class="font-bold mb-1">Please correct the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.companies.store') }}" class="space-y-4">
                @csrf

                <!-- Symbol & Currency Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="symbol" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Stock Symbol <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="symbol"
                               name="symbol"
                               value="{{ old('symbol') }}"
                               placeholder="e.g. AAPL, TSMC, ENGRO"
                               required
                               maxlength="20"
                               class="w-full px-3 py-2 text-xs uppercase font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <p class="text-[11px] text-slate-400 mt-1">Unique ticker code (will be automatically capitalized).</p>
                    </div>

                    <div>
                        <label for="currency" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Trading Currency <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="currency"
                               name="currency"
                               value="{{ old('currency', 'USD') }}"
                               placeholder="e.g. USD, PKR"
                               required
                               maxlength="10"
                               class="w-full px-3 py-2 text-xs uppercase font-mono rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Company Name -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Company Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           placeholder="e.g. Apple Inc., Engro Corporation Limited"
                           required
                           maxlength="150"
                           class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                </div>

                <!-- Exchange & Sector Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="exchange" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Exchange <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="exchange"
                               name="exchange"
                               value="{{ old('exchange', 'NASDAQ') }}"
                               placeholder="e.g. NASDAQ, NYSE, PSX"
                               required
                               maxlength="20"
                               class="w-full px-3 py-2 text-xs uppercase rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="sector" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Sector (Optional)
                        </label>
                        <input type="text"
                               id="sector"
                               name="sector"
                               value="{{ old('sector') }}"
                               placeholder="e.g. Technology, Energy, Financials"
                               maxlength="50"
                               class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Active Status -->
                <div class="pt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                               class="rounded border-slate-300 dark:border-slate-700 text-emerald-600 focus:ring-emerald-500 dark:bg-slate-900">
                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">Set as Active Company</span>
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                    <a href="{{ route('admin.companies.index') }}"
                       class="px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg border border-slate-300 dark:border-slate-600 transition-colors">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        Save Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
