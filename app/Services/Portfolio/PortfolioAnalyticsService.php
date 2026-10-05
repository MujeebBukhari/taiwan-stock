<?php

namespace App\Services\Portfolio;

use App\Enums\TransactionType;
use App\Models\StockPosition;
use App\Models\User;
use App\Services\Financial\FinancialMath;

class PortfolioAnalyticsService
{
    public function __construct(
        protected PortfolioReportService $reportService,
        protected PortfolioService $portfolioService
    ) {}

    /**
     * Top-level summary metrics for the analytics dashboard.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     total_buy_transactions: int,
     *     total_sell_transactions: int,
     *     total_realized_profit: string,
     *     total_inventory_cost: string,
     *     active_positions_count: int,
     *     companies_held_count: int
     * }
     */
    public function getSummaryMetrics(User $user, array $filters = []): array
    {
        $portfolioSummary = $this->portfolioService->getSummary($user);
        $transactionActivity = $this->reportService->getTransactionActivity($user, $filters);

        return [
            'total_buy_transactions' => $transactionActivity['total_buy_transactions'],
            'total_sell_transactions' => $transactionActivity['total_sell_transactions'],
            'total_realized_profit' => $transactionActivity['total_realized_profit'],
            'total_inventory_cost' => $portfolioSummary['total_inventory_cost'],
            'active_positions_count' => $portfolioSummary['active_positions_count'],
            'companies_held_count' => $portfolioSummary['companies_held_count'],
        ];
    }

    /**
     * Chart: Monthly BUY vs SELL Trading Activity.
     * Grouped chronologically by Year + Month (years remain separate).
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     labels: list<string>,
     *     datasets: list<array{
     *         label: string,
     *         data: list<int>,
     *         backgroundColor: string,
     *         borderRadius: int
     *     }>,
     *     has_data: bool
     * }
     */
    public function getMonthlyTradingActivityChart(User $user, array $filters = []): array
    {
        $monthlyReport = $this->reportService->getMonthlyReport($user, $filters);

        $labels = [];
        $buyCounts = [];
        $sellCounts = [];

        foreach ($monthlyReport as $item) {
            $labels[] = $item['month_name'];
            $buyCounts[] = $item['buy_transactions_count'];
            $sellCounts[] = $item['sell_transactions_count'];
        }

        $hasData = count($labels) > 0;

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'BUY Orders',
                    'data' => $buyCounts,
                    'backgroundColor' => '#10b981', // emerald-500
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'SELL Orders',
                    'data' => $sellCounts,
                    'backgroundColor' => '#f43f5e', // rose-500
                    'borderRadius' => 4,
                ],
            ],
            'has_data' => $hasData,
        ];
    }

    /**
     * Chart: Monthly Realized Profit / Loss.
     * Uses stored immutable SELL transaction realized_profit values.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     labels: list<string>,
     *     datasets: list<array{
     *         label: string,
     *         data: list<float>,
     *         backgroundColor: list<string>,
     *         borderRadius: int
     *     }>,
     *     has_data: bool,
     *     total_realized_profit: string
     * }
     */
    public function getMonthlyRealizedProfitChart(User $user, array $filters = []): array
    {
        // If explicitly filtered to BUY only, realized profit is empty
        if (! empty($filters['type']) && strtoupper((string) $filters['type']) === TransactionType::BUY->value) {
            return [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => 'Realized Profit / (Loss)',
                        'data' => [],
                        'backgroundColor' => [],
                        'borderRadius' => 4,
                    ],
                ],
                'has_data' => false,
                'total_realized_profit' => '0.00000000',
            ];
        }

        $monthlyReport = $this->reportService->getMonthlyReport($user, $filters);

        $labels = [];
        $profitData = [];
        $colors = [];
        $cumulativeProfit = '0.00000000';

        foreach ($monthlyReport as $item) {
            // Only include months that had transactions
            $labels[] = $item['month_name'];
            $profitFloat = (float) $item['total_realized_profit'];
            $profitData[] = $profitFloat;
            $colors[] = $profitFloat >= 0 ? '#10b981' : '#f43f5e';

            $cumulativeProfit = FinancialMath::add(
                $cumulativeProfit,
                $item['total_realized_profit'],
                FinancialMath::DEFAULT_SCALE
            );
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Realized Profit / (Loss)',
                    'data' => $profitData,
                    'backgroundColor' => $colors,
                    'borderRadius' => 4,
                ],
            ],
            'has_data' => count($labels) > 0,
            'total_realized_profit' => $cumulativeProfit,
        ];
    }

    /**
     * Chart & Table: Realized Profit / Loss by Company.
     * Displays all companies with SELL activity.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     labels: list<string>,
     *     datasets: list<array{
     *         label: string,
     *         data: list<float>,
     *         backgroundColor: list<string>,
     *         borderRadius: int
     *     }>,
     *     has_data: bool,
     *     table_data: list<array{
     *         company_name: string,
     *         symbol: string,
     *         currency: string,
     *         sell_transactions_count: int,
     *         total_sell_revenue: string,
     *         total_sell_cost_basis: string,
     *         realized_profit: string
     *     }>
     * }
     */
    public function getCompanyRealizedProfitChart(User $user, array $filters = []): array
    {
        $realizedList = $this->reportService->getRealizedProfitAnalysis($user, $filters);

        $labels = [];
        $profitValues = [];
        $colors = [];
        $tableData = [];

        foreach ($realizedList as $item) {
            $labels[] = $item['company']->symbol;
            $profitFloat = (float) $item['realized_profit'];
            $profitValues[] = $profitFloat;
            $colors[] = $profitFloat >= 0 ? '#10b981' : '#f43f5e';

            $tableData[] = [
                'company_name' => $item['company']->name,
                'symbol' => $item['company']->symbol,
                'currency' => $item['company']->currency,
                'sell_transactions_count' => $item['sell_transactions_count'],
                'total_sell_revenue' => $item['total_sell_revenue'],
                'total_sell_cost_basis' => $item['total_sell_cost_basis'],
                'realized_profit' => $item['realized_profit'],
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Realized Profit / Loss',
                    'data' => $profitValues,
                    'backgroundColor' => $colors,
                    'borderRadius' => 4,
                ],
            ],
            'has_data' => count($labels) > 0,
            'table_data' => $tableData,
        ];
    }

    /**
     * Chart: Inventory Cost Basis by Company.
     * Uses StockPosition.inventory_amount. Only active positions (quantity > 0) appear.
     * Explicitly labeled as Inventory Cost Basis (NOT market value).
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     labels: list<string>,
     *     datasets: list<array{
     *         label: string,
     *         data: list<float>,
     *         backgroundColor: list<string>,
     *         borderWidth: int
     *     }>,
     *     has_data: bool,
     *     total_cost_basis: string,
     *     table_data: list<array{
     *         company_name: string,
     *         symbol: string,
     *         exchange: string,
     *         quantity: string,
     *         average_price: string,
     *         inventory_amount: string
     *     }>
     * }
     */
    public function getCostBasisDistributionChart(User $user, array $filters = []): array
    {
        $positionQuery = StockPosition::query()
            ->forUser($user)
            ->where('quantity', '>', 0)
            ->with('company');

        if (! empty($filters['company_id'])) {
            $positionQuery->where('company_id', $filters['company_id']);
        }
        if (! empty($filters['exchange'])) {
            $exchange = trim((string) $filters['exchange']);
            $positionQuery->whereHas('company', fn ($q) => $q->where('exchange', $exchange));
        }
        if (! empty($filters['sector'])) {
            $sector = trim((string) $filters['sector']);
            $positionQuery->whereHas('company', fn ($q) => $q->where('sector', $sector));
        }

        $positions = $positionQuery->get()->sortByDesc(function ($pos) {
            return (float) $pos->inventory_amount;
        });

        // Curated harmonious color palette for distribution segments
        $palette = [
            '#0284c7', '#0d9488', '#10b981', '#f59e0b', '#8b5cf6',
            '#ec4899', '#6366f1', '#14b8a6', '#f97316', '#06b6d4',
            '#84cc16', '#a855f7', '#64748b', '#e11d48', '#3b82f6',
        ];

        $labels = [];
        $costValues = [];
        $colors = [];
        $tableData = [];
        $totalCostBasis = '0.00000000';
        $idx = 0;

        foreach ($positions as $position) {
            if (! $position->company) {
                continue;
            }

            $labels[] = $position->company->symbol;
            $costFloat = (float) $position->inventory_amount;
            $costValues[] = $costFloat;
            $colors[] = $palette[$idx % count($palette)];
            $idx++;

            $totalCostBasis = FinancialMath::add(
                $totalCostBasis,
                (string) $position->inventory_amount,
                FinancialMath::DEFAULT_SCALE
            );

            $tableData[] = [
                'company_name' => $position->company->name,
                'symbol' => $position->company->symbol,
                'exchange' => $position->company->exchange,
                'quantity' => (string) $position->quantity,
                'average_price' => (string) $position->average_price,
                'inventory_amount' => (string) $position->inventory_amount,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Inventory Cost Basis',
                    'data' => $costValues,
                    'backgroundColor' => $colors,
                    'borderWidth' => 1,
                ],
            ],
            'has_data' => count($labels) > 0,
            'total_cost_basis' => $totalCostBasis,
            'table_data' => $tableData,
        ];
    }

    /**
     * Chart: Transaction Distribution (BUY count vs SELL count).
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     labels: list<string>,
     *     datasets: list<array{
     *         data: list<int>,
     *         backgroundColor: list<string>,
     *         borderWidth: int
     *     }>,
     *     buy_count: int,
     *     sell_count: int,
     *     total_count: int,
     *     has_data: bool
     * }
     */
    public function getTransactionDistributionChart(User $user, array $filters = []): array
    {
        $activity = $this->reportService->getTransactionActivity($user, $filters);

        $buyCount = $activity['total_buy_transactions'];
        $sellCount = $activity['total_sell_transactions'];
        $totalCount = $buyCount + $sellCount;

        return [
            'labels' => ['BUY Orders', 'SELL Orders'],
            'datasets' => [
                [
                    'data' => [$buyCount, $sellCount],
                    'backgroundColor' => ['#10b981', '#f43f5e'],
                    'borderWidth' => 1,
                ],
            ],
            'buy_count' => $buyCount,
            'sell_count' => $sellCount,
            'total_count' => $totalCount,
            'has_data' => $totalCount > 0,
        ];
    }

    /**
     * Filter options for dropdowns in analytics view.
     *
     * @return array<string, mixed>
     */
    public function getFilterOptions(User $user): array
    {
        return $this->reportService->getFilterOptions($user);
    }
}
