<?php

namespace App\Http\Controllers;

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
}
