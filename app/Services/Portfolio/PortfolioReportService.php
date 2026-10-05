<?php

namespace App\Services\Portfolio;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PortfolioReportService
 *
 * Core domain service for computing historical accounting analytics, transaction summaries,
 * company-wise metrics, and period-based (monthly/annual) performance reports.
 */
class PortfolioReportService
{
    public function __construct(
        protected PortfolioService $portfolioService
    ) {}

    /**
     * Get authoritative portfolio summary metrics (reuses existing portfolio foundation).
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
     * Build the filtered transaction query scoped to the authenticated user.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredTransactionsQuery(User $user, array $filters = []): Builder
    {
        $query = StockTransaction::query()
            ->where('user_id', $user->id)
            ->with('company');

        // 1. Date From (inclusive)
        if (! empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        // 2. Date To (inclusive)
        if (! empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
        }

        // 3. Company filter
        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        // 4. Exchange filter
        if (! empty($filters['exchange'])) {
            $exchange = trim((string) $filters['exchange']);
            $query->whereHas('company', function (Builder $q) use ($exchange) {
                $q->where('exchange', $exchange);
            });
        }

        // 5. Sector filter
        if (! empty($filters['sector'])) {
            $sector = trim((string) $filters['sector']);
            $query->whereHas('company', function (Builder $q) use ($sector) {
                $q->where('sector', $sector);
            });
        }

        // 6. Transaction Type filter
        if (! empty($filters['type']) && strtoupper($filters['type']) !== 'ALL') {
            $type = strtoupper(trim((string) $filters['type']));
            if (in_array($type, [TransactionType::BUY->value, TransactionType::SELL->value], true)) {
                $query->where('type', $type);
            }
        }

        return $query;
    }

    /**
     * Aggregate lifetime or filtered transaction activity metrics.
     *
     * @param  array<string, mixed>  $filters
     * @return array{
     *     total_buy_transactions: int,
     *     total_sell_transactions: int,
     *     total_transactions: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_sell_cost: string,
     *     total_realized_profit: string
     * }
     */
    public function getTransactionActivity(User $user, array $filters = []): array
    {
        $transactions = $this->getFilteredTransactionsQuery($user, $filters)
            ->get(['type', 'quantity', 'amount', 'cost', 'realized_profit']);

        $totalBuyTransactions = 0;
        $totalSellTransactions = 0;
        $totalBuyQuantity = '0.00000000';
        $totalSellQuantity = '0.00000000';
        $totalBuyAmount = '0.00000000';
        $totalSellAmount = '0.00000000';
        $totalSellCost = '0.00000000';
        $totalRealizedProfit = '0.00000000';

        foreach ($transactions as $tx) {
            $qty = (string) $tx->quantity;
            $amt = (string) $tx->amount;
            $cost = (string) $tx->cost;
            $profit = (string) $tx->realized_profit;

            if ($tx->type->isBuy()) {
                $totalBuyTransactions++;
                $totalBuyQuantity = FinancialMath::add($totalBuyQuantity, $qty, FinancialMath::DEFAULT_SCALE);
                $totalBuyAmount = FinancialMath::add($totalBuyAmount, $amt, FinancialMath::DEFAULT_SCALE);
            } elseif ($tx->type->isSell()) {
                $totalSellTransactions++;
                $totalSellQuantity = FinancialMath::add($totalSellQuantity, $qty, FinancialMath::DEFAULT_SCALE);
                $totalSellAmount = FinancialMath::add($totalSellAmount, $amt, FinancialMath::DEFAULT_SCALE);
                $totalSellCost = FinancialMath::add($totalSellCost, $cost, FinancialMath::DEFAULT_SCALE);
                $totalRealizedProfit = FinancialMath::add($totalRealizedProfit, $profit, FinancialMath::DEFAULT_SCALE);
            }
        }

        return [
            'total_buy_transactions' => $totalBuyTransactions,
            'total_sell_transactions' => $totalSellTransactions,
            'total_transactions' => $totalBuyTransactions + $totalSellTransactions,
            'total_buy_quantity' => $totalBuyQuantity,
            'total_sell_quantity' => $totalSellQuantity,
            'total_buy_amount' => $totalBuyAmount,
            'total_sell_amount' => $totalSellAmount,
            'total_sell_cost' => $totalSellCost,
            'total_realized_profit' => $totalRealizedProfit,
        ];
    }

    /**
     * Compute company-wise summary report combining historical transaction activity
     * with authoritative current StockPosition inventory and cost basis.
     *
     * @param  array<string, mixed>  $filters
     * @return list<array{
     *     company: Company,
     *     buy_transactions_count: int,
     *     sell_transactions_count: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_realized_profit: string,
     *     current_quantity: string,
     *     current_inventory_cost: string,
     *     current_wac: string
     * }>
     */
    public function getCompanyWiseReport(User $user, array $filters = []): array
    {
        $transactions = $this->getFilteredTransactionsQuery($user, $filters)
            ->with('company')
            ->get();

        // Also fetch user's positions for these companies (or all user positions matching company filters)
        $positionQuery = StockPosition::query()
            ->forUser($user)
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

        $positions = $positionQuery->get()->keyBy('company_id');

        // Identify all distinct companies from transactions and positions
        $hasTransactionFilters = ! empty($filters['date_from'])
            || ! empty($filters['date_to'])
            || (! empty($filters['type']) && strtoupper($filters['type']) !== 'ALL');

        $companiesMap = [];
        foreach ($transactions as $tx) {
            if ($tx->company) {
                $companiesMap[$tx->company_id] = $tx->company;
            }
        }

        if (! $hasTransactionFilters) {
            foreach ($positions as $companyId => $position) {
                if ($position->company && ! isset($companiesMap[$companyId])) {
                    $companiesMap[$companyId] = $position->company;
                }
            }
        }

        $report = [];

        foreach ($companiesMap as $companyId => $company) {
            $companyTxs = $transactions->where('company_id', $companyId);
            $position = $positions->get($companyId);

            $buyCount = 0;
            $sellCount = 0;
            $totalBuyQty = '0.00000000';
            $totalSellQty = '0.00000000';
            $totalBuyAmt = '0.00000000';
            $totalSellAmt = '0.00000000';
            $totalRealizedProfit = '0.00000000';

            foreach ($companyTxs as $tx) {
                $qty = (string) $tx->quantity;
                $amt = (string) $tx->amount;
                $profit = (string) $tx->realized_profit;

                if ($tx->type->isBuy()) {
                    $buyCount++;
                    $totalBuyQty = FinancialMath::add($totalBuyQty, $qty, FinancialMath::DEFAULT_SCALE);
                    $totalBuyAmt = FinancialMath::add($totalBuyAmt, $amt, FinancialMath::DEFAULT_SCALE);
                } elseif ($tx->type->isSell()) {
                    $sellCount++;
                    $totalSellQty = FinancialMath::add($totalSellQty, $qty, FinancialMath::DEFAULT_SCALE);
                    $totalSellAmt = FinancialMath::add($totalSellAmt, $amt, FinancialMath::DEFAULT_SCALE);
                    $totalRealizedProfit = FinancialMath::add($totalRealizedProfit, $profit, FinancialMath::DEFAULT_SCALE);
                }
            }

            $currentQty = $position ? (string) $position->quantity : '0.00000000';
            $currentCost = $position ? (string) $position->inventory_amount : '0.00000000';
            $currentWac = $position ? (string) $position->average_price : '0.00000000';

            $report[] = [
                'company' => $company,
                'buy_transactions_count' => $buyCount,
                'sell_transactions_count' => $sellCount,
                'total_buy_quantity' => $totalBuyQty,
                'total_sell_quantity' => $totalSellQty,
                'total_buy_amount' => $totalBuyAmt,
                'total_sell_amount' => $totalSellAmt,
                'total_realized_profit' => $totalRealizedProfit,
                'current_quantity' => $currentQty,
                'current_inventory_cost' => $currentCost,
                'current_wac' => $currentWac,
            ];
        }

        // Sort by company symbol alphabetically
        usort($report, fn ($a, $b) => strcmp($a['company']->symbol, $b['company']->symbol));

        return $report;
    }

    /**
     * Compute monthly historical activity report grouped by calendar Year + Month.
     * Sorted chronologically (oldest to newest).
     *
     * @param  array<string, mixed>  $filters
     * @return list<array{
     *     period_key: string,
     *     month_name: string,
     *     year: int,
     *     month: int,
     *     buy_transactions_count: int,
     *     sell_transactions_count: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_realized_profit: string
     * }>
     */
    public function getMonthlyReport(User $user, array $filters = []): array
    {
        $transactions = $this->getFilteredTransactionsQuery($user, $filters)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $monthlyGroups = [];

        foreach ($transactions as $tx) {
            $date = Carbon::parse($tx->transaction_date);
            $periodKey = $date->format('Y-m'); // e.g. "2026-01"

            if (! isset($monthlyGroups[$periodKey])) {
                $monthlyGroups[$periodKey] = [
                    'period_key' => $periodKey,
                    'month_name' => $date->format('F Y'), // e.g. "January 2026"
                    'year' => (int) $date->format('Y'),
                    'month' => (int) $date->format('n'),
                    'buy_transactions_count' => 0,
                    'sell_transactions_count' => 0,
                    'total_buy_quantity' => '0.00000000',
                    'total_sell_quantity' => '0.00000000',
                    'total_buy_amount' => '0.00000000',
                    'total_sell_amount' => '0.00000000',
                    'total_realized_profit' => '0.00000000',
                ];
            }

            $qty = (string) $tx->quantity;
            $amt = (string) $tx->amount;
            $profit = (string) $tx->realized_profit;

            if ($tx->type->isBuy()) {
                $monthlyGroups[$periodKey]['buy_transactions_count']++;
                $monthlyGroups[$periodKey]['total_buy_quantity'] = FinancialMath::add(
                    $monthlyGroups[$periodKey]['total_buy_quantity'],
                    $qty,
                    FinancialMath::DEFAULT_SCALE
                );
                $monthlyGroups[$periodKey]['total_buy_amount'] = FinancialMath::add(
                    $monthlyGroups[$periodKey]['total_buy_amount'],
                    $amt,
                    FinancialMath::DEFAULT_SCALE
                );
            } elseif ($tx->type->isSell()) {
                $monthlyGroups[$periodKey]['sell_transactions_count']++;
                $monthlyGroups[$periodKey]['total_sell_quantity'] = FinancialMath::add(
                    $monthlyGroups[$periodKey]['total_sell_quantity'],
                    $qty,
                    FinancialMath::DEFAULT_SCALE
                );
                $monthlyGroups[$periodKey]['total_sell_amount'] = FinancialMath::add(
                    $monthlyGroups[$periodKey]['total_sell_amount'],
                    $amt,
                    FinancialMath::DEFAULT_SCALE
                );
                $monthlyGroups[$periodKey]['total_realized_profit'] = FinancialMath::add(
                    $monthlyGroups[$periodKey]['total_realized_profit'],
                    $profit,
                    FinancialMath::DEFAULT_SCALE
                );
            }
        }

        // Sort chronologically (oldest to newest)
        ksort($monthlyGroups);

        return array_values($monthlyGroups);
    }

    /**
     * Compute annual summary report grouped by calendar Year.
     * Sorted chronologically (oldest to newest).
     *
     * @param  array<string, mixed>  $filters
     * @return list<array{
     *     year: int,
     *     buy_transactions_count: int,
     *     sell_transactions_count: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_realized_profit: string
     * }>
     */
    public function getAnnualReport(User $user, array $filters = []): array
    {
        $transactions = $this->getFilteredTransactionsQuery($user, $filters)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $annualGroups = [];

        foreach ($transactions as $tx) {
            $year = (int) Carbon::parse($tx->transaction_date)->format('Y');

            if (! isset($annualGroups[$year])) {
                $annualGroups[$year] = [
                    'year' => $year,
                    'buy_transactions_count' => 0,
                    'sell_transactions_count' => 0,
                    'total_buy_quantity' => '0.00000000',
                    'total_sell_quantity' => '0.00000000',
                    'total_buy_amount' => '0.00000000',
                    'total_sell_amount' => '0.00000000',
                    'total_realized_profit' => '0.00000000',
                ];
            }

            $qty = (string) $tx->quantity;
            $amt = (string) $tx->amount;
            $profit = (string) $tx->realized_profit;

            if ($tx->type->isBuy()) {
                $annualGroups[$year]['buy_transactions_count']++;
                $annualGroups[$year]['total_buy_quantity'] = FinancialMath::add(
                    $annualGroups[$year]['total_buy_quantity'],
                    $qty,
                    FinancialMath::DEFAULT_SCALE
                );
                $annualGroups[$year]['total_buy_amount'] = FinancialMath::add(
                    $annualGroups[$year]['total_buy_amount'],
                    $amt,
                    FinancialMath::DEFAULT_SCALE
                );
            } elseif ($tx->type->isSell()) {
                $annualGroups[$year]['sell_transactions_count']++;
                $annualGroups[$year]['total_sell_quantity'] = FinancialMath::add(
                    $annualGroups[$year]['total_sell_quantity'],
                    $qty,
                    FinancialMath::DEFAULT_SCALE
                );
                $annualGroups[$year]['total_sell_amount'] = FinancialMath::add(
                    $annualGroups[$year]['total_sell_amount'],
                    $amt,
                    FinancialMath::DEFAULT_SCALE
                );
                $annualGroups[$year]['total_realized_profit'] = FinancialMath::add(
                    $annualGroups[$year]['total_realized_profit'],
                    $profit,
                    FinancialMath::DEFAULT_SCALE
                );
            }
        }

        // Sort chronologically (oldest to newest)
        ksort($annualGroups);

        return array_values($annualGroups);
    }

    /**
     * Compute realized profit/loss analysis by company using stored immutable SELL records.
     *
     * @param  array<string, mixed>  $filters
     * @return list<array{
     *     company: Company,
     *     total_sell_revenue: string,
     *     total_sell_cost_basis: string,
     *     realized_profit: string,
     *     sell_transactions_count: int
     * }>
     */
    public function getRealizedProfitAnalysis(User $user, array $filters = []): array
    {
        // If user specifically requested BUY orders, no SELL transactions exist for this filter
        if (! empty($filters['type']) && strtoupper((string) $filters['type']) === TransactionType::BUY->value) {
            return [];
        }

        // Enforce SELL transaction query
        $sellFilters = array_merge($filters, ['type' => TransactionType::SELL->value]);

        $sellTransactions = $this->getFilteredTransactionsQuery($user, $sellFilters)
            ->with('company')
            ->get();

        $companyGroups = [];

        foreach ($sellTransactions as $tx) {
            $companyId = $tx->company_id;

            if (! isset($companyGroups[$companyId])) {
                $companyGroups[$companyId] = [
                    'company' => $tx->company,
                    'total_sell_revenue' => '0.00000000',
                    'total_sell_cost_basis' => '0.00000000',
                    'realized_profit' => '0.00000000',
                    'sell_transactions_count' => 0,
                ];
            }

            $amt = (string) $tx->amount;
            $cost = (string) $tx->cost;
            $profit = (string) $tx->realized_profit;

            $companyGroups[$companyId]['sell_transactions_count']++;
            $companyGroups[$companyId]['total_sell_revenue'] = FinancialMath::add(
                $companyGroups[$companyId]['total_sell_revenue'],
                $amt,
                FinancialMath::DEFAULT_SCALE
            );
            $companyGroups[$companyId]['total_sell_cost_basis'] = FinancialMath::add(
                $companyGroups[$companyId]['total_sell_cost_basis'],
                $cost,
                FinancialMath::DEFAULT_SCALE
            );
            $companyGroups[$companyId]['realized_profit'] = FinancialMath::add(
                $companyGroups[$companyId]['realized_profit'],
                $profit,
                FinancialMath::DEFAULT_SCALE
            );
        }

        // Sort by realized profit descending (highest profit first)
        usort($companyGroups, function ($a, $b) {
            if (FinancialMath::greaterThan($a['realized_profit'], $b['realized_profit'])) {
                return -1;
            }
            if (FinancialMath::lessThan($a['realized_profit'], $b['realized_profit'])) {
                return 1;
            }

            return strcmp($a['company']->symbol, $b['company']->symbol);
        });

        return array_values($companyGroups);
    }

    /**
     * Get dropdown filter options available for the user's trading history.
     *
     * @return array{
     *     companies: Collection<int, Company>,
     *     exchanges: list<string>,
     *     sectors: list<string>
     * }
     */
    public function getFilterOptions(User $user): array
    {
        $companyIds = StockTransaction::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->pluck('company_id');

        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->orderBy('symbol')
            ->get();

        $exchanges = Company::query()
            ->whereIn('id', $companyIds)
            ->distinct()
            ->orderBy('exchange')
            ->pluck('exchange')
            ->values()
            ->all();

        $sectors = Company::query()
            ->whereIn('id', $companyIds)
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->distinct()
            ->orderBy('sector')
            ->pluck('sector')
            ->values()
            ->all();

        return [
            'companies' => $companies,
            'exchanges' => $exchanges,
            'sectors' => $sectors,
        ];
    }
}
