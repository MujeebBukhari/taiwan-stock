<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockTransactionController;
use App\Http\Controllers\StockWorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
Route::post('/locale', [LocaleController::class, 'switch'])->name('locale.update');

Route::get('/currency/{currency}', [CurrencyController::class, 'switch'])->name('currency.switch');
Route::post('/currency', [CurrencyController::class, 'switch'])->name('currency.update');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/stocks', [CompanyController::class, 'index'])->name('stocks.index');

    // Companies viewable by authenticated users (Active only for normal users, searchable & filterable)
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');

    // Stock Workspace for individual company holdings and transaction history
    Route::get('/stocks/{company}', [StockWorkspaceController::class, 'show'])->name('stocks.show');

    // BUY Transaction Routes
    Route::get('/stocks/{company}/buy', [StockTransactionController::class, 'create'])->name('stocks.buy.create');
    Route::post('/stocks/{company}/buy', [StockTransactionController::class, 'store'])->name('stocks.buy.store');

    // SELL Transaction Routes
    Route::get('/stocks/{company}/sell', [StockTransactionController::class, 'createSell'])->name('stocks.sell.create');
    Route::post('/stocks/{company}/sell', [StockTransactionController::class, 'storeSell'])->name('stocks.sell.store');

    // Transaction Edit, Update & Delete Routes
    Route::get('/stocks/{company}/transactions/{transaction}/edit', [StockTransactionController::class, 'edit'])->name('stocks.transactions.edit');
    Route::put('/stocks/{company}/transactions/{transaction}', [StockTransactionController::class, 'update'])->name('stocks.transactions.update');
    Route::delete('/stocks/{company}/transactions/{transaction}', [StockTransactionController::class, 'destroy'])->name('stocks.transactions.destroy');

    // Admin Company Management (strictly restricted to administrators)
    Route::middleware('can:manage-stocks')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/create', [CompanyController::class, 'create'])->name('companies.create');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::get('/companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
        Route::patch('/companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus'])->name('companies.toggle-status');
    });

    Route::get('/transactions', function () {
        return view('transactions.index');
    })->name('transactions.index');

    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('/portfolio/positions/{position}', [PortfolioController::class, 'show'])->name('portfolio.positions.show');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');

    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
