<x-app-layout>
    <x-slot name="header">
        Dashboard
    </x-slot>

    <div class="space-y-6">
        <!-- Welcome Banner -->
        <div class="p-6 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Welcome back, {{ auth()->user()->name }}</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Stock Portfolio and Transaction Management System &bull; Phase 1 Foundation
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ auth()->user()->isAdmin() ? 'bg-purple-100 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border border-purple-300 dark:border-purple-800' : 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' }}">
                        Role: {{ auth()->user()->role ?? 'USER' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        Status: Active
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Modules Status Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Financial Precision</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300">Ready</span>
                </div>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-3">BCMath Engine</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Arbitrary-precision math service configured with 8-decimal intermediate accuracy.</p>
            </div>

            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Database Engine</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">SQLite WAL</span>
                </div>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-3">Concurrency Ready</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Multi-driver support: Local SQLite WAL active, MySQL enterprise configuration preserved.</p>
            </div>

            <div class="p-5 rounded-xl bg-white dark:bg-slate-800 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Authorization</span>
                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300">Active</span>
                </div>
                <p class="text-lg font-bold text-slate-900 dark:text-white mt-3">RBAC Foundation</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Admin & User roles with session-level active account verification.</p>
            </div>
        </div>
    </div>
</x-app-layout>
