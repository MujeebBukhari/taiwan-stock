<x-app-layout>
    <x-slot name="header">
        Companies Master
    </x-slot>

    <div class="space-y-6">
        <!-- Toolbar: Title, Stats, and Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div>
                <h2 class="text-lg font-bold text-slate-900 dark:text-white">Stock / Company Master</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Browse and manage listed companies, symbols, exchanges, and sectors.
                </p>
            </div>

            @can('manage-stocks')
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.companies.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Company</span>
                    </a>
                </div>
            @endcan
        </div>

        <!-- Filter & Search Bar -->
        <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <form method="GET" action="{{ route('companies.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search input -->
                <div class="lg:col-span-2">
                    <label for="search" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Search Symbol or Name</label>
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
                               class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    </div>
                </div>

                <!-- Exchange filter -->
                <div>
                    <label for="exchange" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Exchange</label>
                    <select id="exchange"
                            name="exchange"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">All Exchanges</option>
                        @foreach ($exchanges as $exchange)
                            <option value="{{ $exchange }}" {{ ($filters['exchange'] ?? '') === $exchange ? 'selected' : '' }}>
                                {{ $exchange }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Sector filter -->
                <div>
                    <label for="sector" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Sector</label>
                    <select id="sector"
                            name="sector"
                            class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                        <option value="">All Sectors</option>
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
                            <label for="status" class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">Status</label>
                            <select id="status"
                                    name="status"
                                    class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="">All Statuses</option>
                                <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active Only</option>
                                <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                            </select>
                        </div>
                    @endif

                    <button type="submit"
                            class="px-4 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg transition-colors">
                        Filter
                    </button>

                    @if (!empty($filters['search']) || !empty($filters['exchange']) || !empty($filters['sector']) || !empty($filters['status']))
                        <a href="{{ route('companies.index') }}"
                           class="px-3 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg border border-slate-300 dark:border-slate-600 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Companies Table -->
        <div class="rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-semibold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Symbol</th>
                            <th class="py-3 px-4">Company Name</th>
                            <th class="py-3 px-4">Exchange</th>
                            <th class="py-3 px-4">Sector</th>
                            <th class="py-3 px-4">Currency</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @forelse ($companies as $index => $company)
                            <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-900/40 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400 font-mono text-[11px]">
                                    {{ $companies->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 dark:text-white">
                                    <a href="{{ route('stocks.show', $company) }}" class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 hover:border-emerald-500 hover:text-emerald-600 transition-colors">
                                        {{ $company->symbol }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                    <a href="{{ route('stocks.show', $company) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline transition-colors">
                                        {{ $company->name }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-600 dark:text-slate-300 font-medium">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $company->exchange === 'PSX' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800' : 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800' }}">
                                        {{ $company->exchange }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                                    {{ $company->sector ?? '—' }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 dark:text-slate-300">
                                    {{ $company->currency }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($company->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('stocks.show', $company) }}"
                                           class="px-2.5 py-1 text-xs font-semibold rounded text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                            Workspace
                                        </a>

                                        @can('manage-stocks')
                                            <a href="{{ route('admin.companies.edit', $company) }}"
                                               class="px-2.5 py-1 text-xs font-semibold rounded text-slate-700 dark:text-slate-200 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                                Edit
                                            </a>

                                            <form method="POST" action="{{ route('admin.companies.toggle-status', $company) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="px-2.5 py-1 text-xs font-semibold rounded transition-colors {{ $company->is_active ? 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-800' : 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 border border-emerald-200 dark:border-emerald-800' }}">
                                                    {{ $company->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 8 : 7 }}" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm font-medium">No companies found matching your criteria.</p>
                                    <p class="text-xs mt-1 text-slate-400">Try adjusting your search query or filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="p-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-900/40">
                {{ $companies->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
