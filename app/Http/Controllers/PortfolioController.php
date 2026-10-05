<?php

namespace App\Http\Controllers;

use App\Models\StockPosition;
use App\Services\Portfolio\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    /**
     * Display the authenticated user's portfolio dashboard and positions.
     */
    public function index(Request $request, PortfolioService $portfolioService): View
    {
        $user = $request->user();

        $filters = [
            'search' => $request->query('search'),
            'exchange' => $request->query('exchange'),
            'sector' => $request->query('sector'),
            'status' => $request->query('status', 'all'),
        ];

        // 1. Authoritative portfolio summary metrics
        $summary = $portfolioService->getSummary($user);

        // 2. Filtered and paginated positions
        $positions = $portfolioService->getPositions($user, $filters, 15);

        // 3. Dropdown options for filtering
        $filterOptions = $portfolioService->getFilterOptions($user);

        return view('portfolio.index', [
            'summary' => $summary,
            'positions' => $positions,
            'filters' => $filters,
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Display the specified stock position and transaction ledger for the authenticated user.
     */
    public function show(Request $request, StockPosition $position, PortfolioService $portfolioService): View
    {
        // 1. Authorize: user must be active and strictly own the position
        abort_unless($request->user()->can('viewPortfolio', $position), 403, 'Unauthorized position access.');

        // 2. Eager load company to prevent N+1 queries
        $position->loadMissing('company');

        // 3. Compute transaction summaries and fetch ledgers
        $transactionSummary = $portfolioService->getPositionTransactionSummary($position);
        $transactions = $portfolioService->getPositionTransactions($position, 15);
        $sellTransactions = $portfolioService->getPositionSellTransactions($position);

        return view('portfolio.positions.show', [
            'position' => $position,
            'company' => $position->company,
            'transactionSummary' => $transactionSummary,
            'transactions' => $transactions,
            'sellTransactions' => $sellTransactions,
        ]);
    }
}
