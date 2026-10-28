<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">
                {{ __('companies.stock_company_master') }}
            </h1>
            <span class="text-xs text-slate-500 font-medium hidden sm:inline-flex items-center gap-1.5">
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                {{ __('companies.browse_manage_desc') }}
            </span>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Toolbar: Title, Stats, and Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 rounded-xl bg-white shadow-card border border-slate-200/90 transition-all duration-150">
            <div>
                <h2 class="text-lg font-bold text-slate-900 tracking-tight">{{ __('companies.stock_company_master') }}</h2>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('companies.browse_manage_desc') }}
                </p>
            </div>

            @can('manage-stocks')
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.companies.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 active:bg-blue-800 transition-all duration-150 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>{{ __('companies.create_company') }}</span>
                    </a>
                </div>
            @endcan
        </div>

        <!-- Filter & Search Bar -->
        <div class="p-5 rounded-xl bg-white shadow-card border border-slate-200/90">
            <form method="GET" action="{{ route('companies.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search input -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('companies.search_symbol_name') }}</label>
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
                               placeholder="e.g. AAPL, TSMC, Apple..."
                               class="w-full pl-9 pr-3.5 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                    </div>
                </div>

                <!-- Exchange filter -->
                <div>
                    <label for="exchange" class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('companies.exchange') }}</label>
                    <select id="exchange"
                            name="exchange"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <option value="">{{ __('companies.all_exchanges') }}</option>
                        @foreach ($exchanges as $exchange)
                            <option value="{{ $exchange }}" {{ ($filters['exchange'] ?? '') === $exchange ? 'selected' : '' }}>
                                {{ $exchange }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sector filter -->
                <div>
                    <label for="sector" class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('companies.sector') }}</label>
                    <select id="sector"
                            name="sector"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                        <option value="">{{ __('companies.all_sectors') }}</option>
                        @foreach ($sectors as $sector)
                            <option value="{{ $sector }}" {{ ($filters['sector'] ?? '') === $sector ? 'selected' : '' }}>
                                {{ $sector }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status filter (Admin only) or Actions button -->
                <div class="flex items-end gap-2">
                    @if ($isAdmin)
                        <div class="flex-1">
                            <label for="status" class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('companies.status') }}</label>
                            <select id="status"
                                    name="status"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-colors">
                                <option value="">{{ __('companies.all_statuses') }}</option>
                                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>{{ __('companies.active_only') }}</option>
                                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('companies.inactive_only') }}</option>
                            </select>
                        </div>
                    @endif

                    <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-lg shadow-sm transition-colors">
                        {{ __('common.filter') }}
                    </button>

                    @if (!empty($filters['search']) || !empty($filters['exchange']) || !empty($filters['sector']) || !empty($filters['status']))
                        <a href="{{ route('companies.index') }}"
                           class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-lg border border-slate-300 transition-colors">
                            {{ __('common.reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Companies Table -->
        <div class="rounded-xl bg-white shadow-card border border-slate-200/90 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-600 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">{{ __('companies.symbol') }}</th>
                            <th class="py-3 px-4">{{ __('companies.company_name') }}</th>
                            <th class="py-3 px-4">{{ __('companies.exchange') }}</th>
                            <th class="py-3 px-4">{{ __('companies.sector') }}</th>
                            <th class="py-3 px-4">{{ __('companies.currency') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('companies.status') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('companies.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($companies as $index => $company)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    {{ $companies->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                    <a href="{{ route('stocks.show', $company) }}" class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 text-slate-800 border border-slate-200 hover:border-blue-400 hover:text-blue-600 transition-colors">
                                        {{ $company->symbol }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 font-medium text-slate-800">
                                    <a href="{{ route('stocks.show', $company) }}" class="font-semibold text-slate-900 hover:text-blue-600 hover:underline transition-colors">
                                        {{ $company->name }}
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 font-medium">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold {{ in_array(strtoupper($company->exchange), ['TWSE', 'TPEX']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                        {{ $company->exchange }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-500">
                                    {{ $company->sector ?? '—' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">
                                    <span class="font-semibold text-slate-800">{{ $company->currency }}</span>
                                    <span class="text-xs text-slate-400">({{ $company->currency_symbol }})</span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if ($company->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ __('companies.active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ __('companies.inactive') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('stocks.show', $company) }}"
                                           class="px-2.5 py-1 text-xs font-semibold rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200/80 transition-colors">
                                            {{ __('stocks.workspace') }}
                                        </a>

                                        @can('manage-stocks')
                                            <a href="{{ route('admin.companies.edit', $company) }}"
                                               class="px-2.5 py-1 text-xs font-semibold rounded-md text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 shadow-xs hover:border-slate-300 transition-colors">
                                                {{ __('common.edit') }}
                                            </a>

                                            <form method="POST" action="{{ route('admin.companies.toggle-status', $company) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-2.5 py-1 text-xs font-semibold rounded-md transition-colors {{ $company->is_active ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200' }}">
                                                    {{ $company->is_active ? __('companies.deactivate') : __('companies.activate') }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}" class="py-12 text-center text-slate-500">
                                    <p class="text-sm font-semibold text-slate-800">{{ __('companies.no_companies_found') }}</p>
                                    <p class="text-xs mt-1 text-slate-400">{{ __('companies.try_adjusting_search') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="p-4 border-t border-slate-200/80 bg-slate-50/50">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
