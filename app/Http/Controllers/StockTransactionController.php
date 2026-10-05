<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuyTransactionRequest;
use App\Models\Company;
use App\Models\StockPosition;
use App\Services\Transactions\BuyStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransactionController extends Controller
{
    /**
     * Show the form for recording a BUY transaction for a given company.
     */
    public function create(Request $request, Company $company): View
    {
        $user = $request->user();

        // Inactive company protection
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot execute transactions for an inactive company.');
        }

        // Fetch user's current position for context in the BUY form
        $position = StockPosition::query()
            ->forUser($user)
            ->forCompany($company)
            ->first();

        return view('stocks.buy', [
            'company' => $company,
            'position' => $position,
        ]);
    }

    /**
     * Authoritative submission handler for executing a BUY order.
     */
    public function store(
        StoreBuyTransactionRequest $request,
        Company $company,
        BuyStock $buyStock
    ): RedirectResponse {
        $user = $request->user();

        // Inactive company protection
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'Cannot execute transactions for an inactive company.');
        }

        // Hand off authoritative calculation and persistence to the domain service
        $buyStock->execute(
            user: $user,
            company: $company,
            quantity: $request->validated('quantity'),
            price: $request->validated('price'),
            transactionDate: $request->validated('transaction_date'),
            notes: $request->validated('notes')
        );

        return redirect()->route('stocks.show', $company)
            ->with('status', 'BUY transaction recorded successfully.');
    }
}
