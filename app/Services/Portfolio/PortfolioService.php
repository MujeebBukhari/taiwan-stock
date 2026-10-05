<?php

namespace App\Services\Portfolio;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class PortfolioService
 *
 * Core domain service for querying, aggregating, and analyzing portfolio holdings.
 * Designed to be reusable across web controllers, reporting modules, and future desktop APIs.
 */
class PortfolioService
{
    /**
     * Compute portfolio summary metrics for the given user with 8-decimal precision.
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
    public function getSummary(User $user): array
    {
        $positions = StockPosition::query()
            ->forUser($user)
            ->get(['quantity', 'inventory_amount', 'realized_profit', 'company_id']);

        $totalInventoryCost = '0.00000000';
        $totalRealizedProfit = '0.00000000';
        $totalQuantity = '0.00000000';
        $activePositionsCount = 0;
        $totalPositionsCount = $positions->count();
        $distinctCompanies = [];

        foreach ($positions as $position) {
            $qtyStr = (string) $position->quantity;
            $invStr = (string) $position->inventory_amount;
            $profitStr = (string) $position->realized_profit;

            $totalInventoryCost = FinancialMath::add($totalInventoryCost, $invStr, FinancialMath::DEFAULT_SCALE);
            $totalRealizedProfit = FinancialMath::add($totalRealizedProfit, $profitStr, FinancialMath::DEFAULT_SCALE);
            $totalQuantity = FinancialMath::add($totalQuantity, $qtyStr, FinancialMath::DEFAULT_SCALE);

            if (FinancialMath::greaterThan($qtyStr, '0')) {
                $activePositionsCount++;
            }

            $distinctCompanies[$position->company_id] = true;
        }

        return [
            'total_inventory_cost' => $totalInventoryCost,
            'total_realized_profit' => $totalRealizedProfit,
            'total_quantity' => $totalQuantity,
            'active_positions_count' => $activePositionsCount,
            'total_positions_count' => $totalPositionsCount,
            'companies_held_count' => count($distinctCompanies),
        ];
    }

    /**
     * Build the filtered database query for a user's stock positions.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPositionsQuery(User $user, array $filters = []): Builder
    {
        $query = StockPosition::query()
            ->forUser($user)
            ->with('company');

        // 1. Search filter (by company name or symbol)
        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->whereHas('company', function (Builder $q) use ($search) {
                $q->where('symbol', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // 2. Exchange filter
        if (! empty($filters['exchange'])) {
            $exchange = trim((string) $filters['exchange']);
            $query->whereHas('company', function (Builder $q) use ($exchange) {
                $q->where('exchange', $exchange);
            });
        }

        // 3. Sector filter
        if (! empty($filters['sector'])) {
            $sector = trim((string) $filters['sector']);
            $query->whereHas('company', function (Builder $q) use ($sector) {
                $q->where('sector', $sector);
            });
        }

        // 4. Status filter ('all', 'holding', 'zero')
        if (! empty($filters['status'])) {
            $status = strtolower(trim((string) $filters['status']));
            if ($status === 'holding') {
                $query->withInventory();
            } elseif ($status === 'zero') {
                $query->empty();
            }
        }

        // Sort holdings (quantity > 0) ahead of closed positions, then by inventory cost descending
        return $query->orderByRaw('CASE WHEN quantity > 0 THEN 0 ELSE 1 END')
            ->orderByDesc('inventory_amount')
            ->orderByDesc('updated_at');
    }

    /**
     * Fetch paginated positions for the user with filters applied.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPositions(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->getPositionsQuery($user, $filters)->paginate($perPage)->withQueryString();
    }

    /**
     * Fetch all active holdings (quantity > 0) for the user.
     */
    public function getActiveHoldings(User $user): Collection
    {
        return StockPosition::query()
            ->forUser($user)
            ->with('company')
            ->withInventory()
            ->orderByDesc('inventory_amount')
            ->get();
    }

    /**
     * Fetch all closed / zero positions (quantity <= 0) for the user.
     */
    public function getZeroPositions(User $user): Collection
    {
        return StockPosition::query()
            ->forUser($user)
            ->with('company')
            ->empty()
            ->orderByDesc('updated_at')
            ->get();
    }

    /**
     * Determine the status label of a position.
     */
    public function getPositionStatus(StockPosition $position): string
    {
        return $position->status();
    }

    /**
     * Retrieve filter options (available exchanges and sectors) for user's positions.
     *
     * @return array{exchanges: list<string>, sectors: list<string>}
     */
    public function getFilterOptions(?User $user = null): array
    {
        $companyIdsQuery = $user ? StockPosition::query()->forUser($user)->select('company_id') : null;

        $exchangeQuery = Company::query()->distinct()->orderBy('exchange');
        $sectorQuery = Company::query()->whereNotNull('sector')->where('sector', '!=', '')->distinct()->orderBy('sector');

        if ($companyIdsQuery && $companyIdsQuery->exists()) {
            $exchangeQuery->whereIn('id', $companyIdsQuery);
            $sectorQuery->whereIn('id', $companyIdsQuery);
        }

        return [
            'exchanges' => $exchangeQuery->pluck('exchange')->values()->all(),
            'sectors' => $sectorQuery->pluck('sector')->values()->all(),
        ];
    }

    /**
     * Compute transaction aggregate summary for a specific position/company of a user.
     *
     * @return array{
     *     total_buy_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_quantity: string,
     *     total_sell_amount: string,
     *     total_sell_cost: string,
     *     total_realized_profit: string,
     *     buy_transactions_count: int,
     *     sell_transactions_count: int
     * }
     */
    public function getPositionTransactionSummary(StockPosition $position): array
    {
        $transactions = StockTransaction::query()
            ->where('user_id', $position->user_id)
            ->where('company_id', $position->company_id)
            ->get(['type', 'quantity', 'amount', 'cost', 'realized_profit']);

        $totalBuyQuantity = '0.00000000';
        $totalBuyAmount = '0.00000000';
        $totalSellQuantity = '0.00000000';
        $totalSellAmount = '0.00000000';
        $totalSellCost = '0.00000000';
        $totalRealizedProfit = '0.00000000';
        $buyCount = 0;
        $sellCount = 0;

        foreach ($transactions as $tx) {
            $qty = (string) $tx->quantity;
            $amt = (string) $tx->amount;
            $cost = (string) $tx->cost;
            $profit = (string) $tx->realized_profit;

            if ($tx->type->isBuy()) {
                $buyCount++;
                $totalBuyQuantity = FinancialMath::add($totalBuyQuantity, $qty, FinancialMath::DEFAULT_SCALE);
                $totalBuyAmount = FinancialMath::add($totalBuyAmount, $amt, FinancialMath::DEFAULT_SCALE);
            } elseif ($tx->type->isSell()) {
                $sellCount++;
                $totalSellQuantity = FinancialMath::add($totalSellQuantity, $qty, FinancialMath::DEFAULT_SCALE);
                $totalSellAmount = FinancialMath::add($totalSellAmount, $amt, FinancialMath::DEFAULT_SCALE);
                $totalSellCost = FinancialMath::add($totalSellCost, $cost, FinancialMath::DEFAULT_SCALE);
                $totalRealizedProfit = FinancialMath::add($totalRealizedProfit, $profit, FinancialMath::DEFAULT_SCALE);
            }
        }

        return [
            'total_buy_quantity' => $totalBuyQuantity,
            'total_buy_amount' => $totalBuyAmount,
            'total_sell_quantity' => $totalSellQuantity,
            'total_sell_amount' => $totalSellAmount,
            'total_sell_cost' => $totalSellCost,
            'total_realized_profit' => $totalRealizedProfit,
            'buy_transactions_count' => $buyCount,
            'sell_transactions_count' => $sellCount,
        ];
    }

    /**
     * Get paginated ledger transactions for this position (newest first).
     */
    public function getPositionTransactions(StockPosition $position, int $perPage = 15): LengthAwarePaginator
    {
        return StockTransaction::query()
            ->where('user_id', $position->user_id)
            ->where('company_id', $position->company_id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get SELL transactions for this position for realized profit breakdown (newest first).
     */
    public function getPositionSellTransactions(StockPosition $position): Collection
    {
        return StockTransaction::query()
            ->where('user_id', $position->user_id)
            ->where('company_id', $position->company_id)
            ->where('type', TransactionType::SELL)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }
}
