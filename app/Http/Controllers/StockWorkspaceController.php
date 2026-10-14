<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Services\MarketData\MarketDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockWorkspaceController extends Controller
{
    /**
     * Display the stock workspace for a specific company, showing the user's
     * position metrics and paginated transaction history ledger.
     *
     * Note: No financial calculations are performed here; position data
     * will be populated by the future BUY/SELL transaction engine.
     */
    public function show(Request $request, Company $company, MarketDataService $marketDataService): View
    {
        $user = $request->user();

        // Inactive company protection: standard users cannot access inactive company workspaces
        if (! $company->is_active && ! $user->isAdmin()) {
            abort(403, 'This company is currently inactive and cannot be accessed.');
        }

        // Retrieve user's current isolated position for this company (if any)
        $position = StockPosition::query()
            ->forUser($user)
            ->forCompany($company)
            ->first();

        // Retrieve user's isolated transaction history ledger for this company
        $transactions = StockTransaction::query()
            ->forUser($user)
            ->forCompany($company)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Retrieve current market quote (cached)
        $marketQuote = $marketDataService->getQuote($company);

        return view('stocks.show', [
            'company' => $company,
            'position' => $position,
            'transactions' => $transactions,
            'marketQuote' => $marketQuote,
        ]);
    }
}
