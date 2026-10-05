<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/stocks', [CompanyController::class, 'index'])->name('stocks.index');

    // Companies viewable by authenticated users (Active only for normal users, searchable & filterable)
    Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');

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

    Route::get('/portfolio', function () {
        return view('portfolio.index');
    })->name('portfolio.index');

    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
