<?php

namespace App\Services\Dashboard;

use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Portfolio\PortfolioAnalyticsService;
use App\Services\Portfolio\PortfolioReportService;
use App\Services\Portfolio\PortfolioService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class DashboardService
 *
 * Dedicated domain service for preparing consolidated dashboard portfolio metrics,
 * recent transactions, active holdings, and realized profit analytics for the authenticated user.
 * Preserves strict user isolation and authoritative financial accounting rules.
 */
class DashboardService
{
    public function __construct(
        protected PortfolioService $portfolioService,
        protected PortfolioReportService $reportService,
        protected PortfolioAnalyticsService $analyticsService
    ) {}

    /**
     * Build the complete data payload for the user's dashboard.
     *
     * @return array{
     *     summary: array{
     *         total_inventory_cost: string,
     *         total_realized_profit: string,
     *         total_quantity: string,
     *         active_positions_count: int,
     *         total_positions_count: int,
     *         companies_held_count: int
     *     },
     *     recent_transactions: Collection<int, StockTransaction>,
     *     current_holdings: Collection<int, StockPosition>,
     *     current_month_activity: array{
     *         month_name: string,
     *         buy_count: int,
     *         sell_count: int,
     *         total_count: int,
     *         realized_profit: string,
     *         buy_amount: string,
     *         sell_amount: string
     *     },
     *     realized_profit_chart: array{
     *         labels: list<string>,
     *         datasets: list<array<string, mixed>>,
     *         has_data: bool,
     *         total_realized_profit: string
     *     },
     *     realized_profit_summary: array{
     *         total_realized_profit: string,
     *         current_month_realized_profit: string,
     *         recent_sells: Collection<int, StockTransaction>
     *     }
     * }
     */
    public function getDashboardData(User $user): array
    {
        return [
            'summary' => $this->getPortfolioSummary($user),
            'recent_transactions' => $this->getRecentTransactions($user, 10),
            'current_holdings' => $this->getCurrentHoldings($user),
            'current_month_activity' => $this->getCurrentMonthActivity($user),
            'realized_profit_chart' => $this->getMonthlyRealizedProfitChart($user, 12),
            'realized_profit_summary' => $this->getRecentRealizedProfitSummary($user),
        ];
    }

    /**
     * Compute authoritative portfolio summary metrics for the given user.
     *
     * @return array{
     *     total_inventory_cost: string,
     *     total_realized_profit: string,
     *     total_quantity: string,
     *     active_positions_count: int,
     *     total_positions_count: int,
     *     companies_held_count: int
     * }
     */
    public function getPortfolioSummary(User $user): array
    {
        return $this->portfolioService->getSummary($user);
    }

    /**
     * Fetch latest transactions for the user (newest first, maximum $limit).
     *
     * @return Collection<int, StockTransaction>
     */
    public function getRecentTransactions(User $user, int $limit = 10): Collection
    {
        return StockTransaction::query()
            ->forUser($user)
            ->with('company')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Fetch active holdings (quantity > 0) sorted by inventory cost descending.
     *
     * @return Collection<int, StockPosition>
     */
    public function getCurrentHoldings(User $user): Collection
    {
        return $this->portfolioService->getActiveHoldings($user);
    }

    /**
     * Aggregate activity for the current calendar month using server date.
     *
     * @return array{
     *     month_name: string,
     *     buy_count: int,
     *     sell_count: int,
     *     total_count: int,
     *     realized_profit: string,
     *     buy_amount: string,
     *     sell_amount: string
     * }
     */
    public function getCurrentMonthActivity(User $user): array
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $activity = $this->reportService->getTransactionActivity($user, [
            'date_from' => $startOfMonth,
            'date_to' => $endOfMonth,
        ]);

        return [
            'month_name' => Carbon::now()->format('F Y'),
            'buy_count' => $activity['total_buy_transactions'],
            'sell_count' => $activity['total_sell_transactions'],
            'total_count' => $activity['total_transactions'],
            'realized_profit' => $activity['total_realized_profit'],
            'buy_amount' => $activity['total_buy_amount'],
            'sell_amount' => $activity['total_sell_amount'],
        ];
    }

    /**
     * Prepare lightweight monthly realized profit chart data (last N months).
     *
     * @return array{
     *     labels: list<string>,
     *     datasets: list<array<string, mixed>>,
     *     has_data: bool,
     *     total_realized_profit: string
     * }
     */
    public function getMonthlyRealizedProfitChart(User $user, int $limitMonths = 12): array
    {
        return $this->analyticsService->getMonthlyRealizedProfitChart($user, [], $limitMonths);
    }

    /**
     * Provide a historical realized profit/loss summary based only on stored SELL transactions.
     *
     * @return array{
     *     total_realized_profit: string,
     *     current_month_realized_profit: string,
     *     recent_sells: Collection<int, StockTransaction>
     * }
     */
    public function getRecentRealizedProfitSummary(User $user): array
    {
        $summary = $this->portfolioService->getSummary($user);
        $currentMonth = $this->getCurrentMonthActivity($user);

        $recentSells = StockTransaction::query()
            ->forUser($user)
            ->sell()
            ->with('company')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return [
            'total_realized_profit' => $summary['total_realized_profit'],
            'current_month_realized_profit' => $currentMonth['realized_profit'],
            'recent_sells' => $recentSells,
        ];
    }
}
