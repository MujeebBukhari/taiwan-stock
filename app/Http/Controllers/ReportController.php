<?php

namespace App\Http\Controllers;

use App\Services\Portfolio\PortfolioReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the portfolio reports and historical accounting analytics dashboard.
     */
    public function index(Request $request, PortfolioReportService $reportService): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'exchange' => ['nullable', 'string'],
            'sector' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'in:all,BUY,SELL,buy,sell'],
        ]);

        $user = $request->user();

        $filters = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'company_id' => $validated['company_id'] ?? null,
            'exchange' => $validated['exchange'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'type' => $validated['type'] ?? 'all',
        ];

        // 1. Authoritative portfolio current metrics (reused from PortfolioService)
        $portfolioSummary = $reportService->getPortfolioSummary($user);

        // 2. Transaction activity totals (filtered)
        $transactionActivity = $reportService->getTransactionActivity($user, $filters);

        // 3. Company-wise breakdown (filtered)
        $companyWiseReport = $reportService->getCompanyWiseReport($user, $filters);

        // 4. Monthly historical activity (filtered)
        $monthlyReport = $reportService->getMonthlyReport($user, $filters);

        // 5. Annual summary (filtered)
        $annualReport = $reportService->getAnnualReport($user, $filters);

        // 6. Realized profit breakdown by company (filtered)
        $realizedProfitAnalysis = $reportService->getRealizedProfitAnalysis($user, $filters);

        // 7. Dropdown filter options from user's history
        $filterOptions = $reportService->getFilterOptions($user);

        return view('reports.index', [
            'filters' => $filters,
            'portfolioSummary' => $portfolioSummary,
            'transactionActivity' => $transactionActivity,
            'companyWiseReport' => $companyWiseReport,
            'monthlyReport' => $monthlyReport,
            'annualReport' => $annualReport,
            'realizedProfitAnalysis' => $realizedProfitAnalysis,
            'filterOptions' => $filterOptions,
        ]);
    }
}
