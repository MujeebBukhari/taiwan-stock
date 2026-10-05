<?php

namespace App\Http\Controllers;

use App\Services\Portfolio\PortfolioAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Display the visual portfolio analytics and charts dashboard.
     */
    public function index(Request $request, PortfolioAnalyticsService $analyticsService): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'exchange' => ['nullable', 'string'],
            'sector' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:all,BUY,SELL,buy,sell'],
        ]);

        $filters = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'company_id' => isset($validated['company_id']) ? (int) $validated['company_id'] : null,
            'exchange' => $validated['exchange'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'type' => $validated['type'] ?? 'all',
        ];

        $user = $request->user();

        $summaryMetrics = $analyticsService->getSummaryMetrics($user, $filters);
        $monthlyTradingActivity = $analyticsService->getMonthlyTradingActivityChart($user, $filters);
        $monthlyRealizedProfit = $analyticsService->getMonthlyRealizedProfitChart($user, $filters);
        $companyRealizedProfit = $analyticsService->getCompanyRealizedProfitChart($user, $filters);
        $costBasisDistribution = $analyticsService->getCostBasisDistributionChart($user, $filters);
        $transactionDistribution = $analyticsService->getTransactionDistributionChart($user, $filters);
        $filterOptions = $analyticsService->getFilterOptions($user);

        return view('analytics.index', [
            'filters' => $filters,
            'summaryMetrics' => $summaryMetrics,
            'monthlyTradingActivity' => $monthlyTradingActivity,
            'monthlyRealizedProfit' => $monthlyRealizedProfit,
            'companyRealizedProfit' => $companyRealizedProfit,
            'costBasisDistribution' => $costBasisDistribution,
            'transactionDistribution' => $transactionDistribution,
            'filterOptions' => $filterOptions,
        ]);
    }
}
