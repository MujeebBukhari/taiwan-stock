<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stocks\StoreCompanyRequest;
use App\Http\Requests\Stocks\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies with search, filtering, and pagination.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Company::query();

        // Search scope (Symbol or Name, case-insensitive partial match)
        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        // Filter by Exchange
        if ($request->filled('exchange')) {
            $query->where('exchange', $request->string('exchange')->toString());
        }

        // Filter by Sector
        if ($request->filled('sector')) {
            $query->where('sector', $request->string('sector')->toString());
        }

        // Filter by Status: non-admins can only see active companies
        if (! $user->isAdmin()) {
            $query->active();
        } else {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'inactive') {
                $query->inactive();
            }
        }

        // Order by symbol ascending
        $companies = $query->orderBy('symbol')->paginate(20)->withQueryString();

        // Distinct filter options for dropdowns
        $exchanges = Company::select('exchange')->distinct()->orderBy('exchange')->pluck('exchange');
        $sectors = Company::whereNotNull('sector')->select('sector')->distinct()->orderBy('sector')->pluck('sector');

        return view('companies.index', [
            'companies' => $companies,
            'exchanges' => $exchanges,
            'sectors' => $sectors,
            'filters' => $request->only(['search', 'exchange', 'sector', 'status']),
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        Gate::authorize('manage-stocks');

        return view('admin.companies.create');
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());

        return redirect()->route('admin.companies.index')
            ->with('status', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): View
    {
        Gate::authorize('manage-stocks');

        return view('admin.companies.edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified company in storage.
     */
    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('admin.companies.index')
            ->with('status', 'Company updated successfully.');
    }

    /**
     * Toggle the active/inactive status of a company.
     * Deletion is disallowed to preserve historical transactional integrity.
     */
    public function toggleStatus(Company $company): RedirectResponse
    {
        Gate::authorize('manage-stocks');

        $company->update([
            'is_active' => ! $company->is_active,
        ]);

        $statusLabel = $company->is_active ? 'activated' : 'deactivated';

        return back()->with('status', "Company {$company->symbol} successfully {$statusLabel}.");
    }
}
