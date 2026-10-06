<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\User;
use App\Services\Dashboard\DashboardService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Company $apple;

    private Company $microsoft;

    private Company $pfizer;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    private DashboardService $dashboardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create([
            'name' => 'Alice Investor',
            'email' => 'alice@example.com',
        ]);

        $this->userB = User::factory()->user()->create([
            'name' => 'Bob Investor',
            'email' => 'bob@example.com',
        ]);

        $this->apple = Company::factory()->create([
            'symbol' => 'AAPL',
            'name' => 'Apple Inc.',
            'exchange' => 'NASDAQ',
            'sector' => 'Technology',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->microsoft = Company::factory()->create([
            'symbol' => 'MSFT',
            'name' => 'Microsoft Corporation',
            'exchange' => 'NASDAQ',
            'sector' => 'Technology',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->pfizer = Company::factory()->create([
            'symbol' => 'PFE',
            'name' => 'Pfizer Inc.',
            'exchange' => 'NYSE',
            'sector' => 'Healthcare',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->buyStock = app(BuyStock::class);
        $this->sellStock = app(SellStock::class);
        $this->dashboardService = app(DashboardService::class);
    }

    /**
     * 1. Guest cannot access dashboard.
     */
    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated user can access dashboard.
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Portfolio Overview');
    }

    /**
     * 3. Dashboard belongs to authenticated user.
     */
    public function test_dashboard_belongs_to_authenticated_user(): void
    {
        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Welcome back, Alice Investor');
    }

    /**
     * 4. User isolation.
     */
    public function test_user_isolation_between_two_users(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');

        $responseUserA = $this->actingAs($this->userA)->get(route('dashboard'));
        $responseUserA->assertSee('AAPL');
        $responseUserA->assertSee('Apple Inc.');

        $responseUserB = $this->actingAs($this->userB)->get(route('dashboard'));
        $responseUserB->assertDontSee('AAPL');
        $responseUserB->assertSee('No active holdings in your portfolio');
        $responseUserB->assertSee('No transactions recorded yet');
    }

    /**
     * 5. Portfolio summary is displayed on dashboard.
     */
    public function test_portfolio_summary_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Inventory Cost Basis');
        $response->assertSee('Realized Profit / Loss');
        $response->assertSee('Total Quantity Held');
        $response->assertSee('Active Positions');
        $response->assertSee('Companies Held');
        $response->assertSee('Total Historical Positions');
    }

    /**
     * 6. Total inventory cost basis is calculated and displayed.
     */
    public function test_total_inventory_cost_basis(): void
    {
        // 10 shares @ 150 = 1500.00
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '150.00000000', '2026-01-10');

        $summary = $this->dashboardService->getPortfolioSummary($this->userA);
        $this->assertSame('1500.00000000', $summary['total_inventory_cost']);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('1,500.00');
    }

    /**
     * 7. Total realized profit/loss is calculated and displayed.
     */
    public function test_total_realized_profit_loss(): void
    {
        // Buy 10 @ 100, Sell 5 @ 140 => Profit = 5 * 40 = +200.00
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '5.00000000', '140.00000000', '2026-01-15');

        $summary = $this->dashboardService->getPortfolioSummary($this->userA);
        $this->assertSame('200.00000000', $summary['total_realized_profit']);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('+200.00');
    }

    /**
     * 8. Active positions count.
     */
    public function test_active_positions_count(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '5.00000000', '200.00000000', '2026-01-12');

        $summary = $this->dashboardService->getPortfolioSummary($this->userA);
        $this->assertSame(2, $summary['active_positions_count']);

        $holdings = $this->dashboardService->getCurrentHoldings($this->userA);
        $this->assertCount(2, $holdings);
    }

    /**
     * 9. Companies held count.
     */
    public function test_companies_held_count(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '110.00000000', '2026-01-12');

        $summary = $this->dashboardService->getPortfolioSummary($this->userA);
        // Only 1 distinct company
        $this->assertSame(1, $summary['companies_held_count']);
    }

    /**
     * 10. Total historical positions includes zero-quantity positions.
     */
    public function test_total_historical_positions(): void
    {
        // Buy then completely liquidate Apple
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '10.00000000', '120.00000000', '2026-01-15');

        // Buy Microsoft and keep it
        $this->buyStock->execute($this->userA, $this->microsoft, '5.00000000', '200.00000000', '2026-01-20');

        $summary = $this->dashboardService->getPortfolioSummary($this->userA);
        $this->assertSame(1, $summary['active_positions_count']);
        $this->assertSame(2, $summary['total_positions_count']);
    }

    /**
     * 11. Recent transactions are fetched and displayed.
     */
    public function test_recent_transactions(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '2.00000000', '110.00000000', '2026-01-15');

        $recent = $this->dashboardService->getRecentTransactions($this->userA);
        $this->assertCount(2, $recent);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('Recent Transactions');
        $response->assertSee('BUY');
        $response->assertSee('SELL');
    }

    /**
     * 12. Maximum 10 recent transactions returned.
     */
    public function test_maximum_ten_recent_transactions(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $day = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $this->buyStock->execute($this->userA, $this->apple, '1.00000000', '100.00000000', "2026-01-{$day}");
        }

        $recent = $this->dashboardService->getRecentTransactions($this->userA, 10);
        $this->assertCount(10, $recent);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('Latest 10 of 10');
    }

    /**
     * 13. Newest transaction ordering.
     */
    public function test_newest_transaction_ordering(): void
    {
        $tx1 = $this->buyStock->execute($this->userA, $this->apple, '1.00000000', '100.00000000', '2026-01-05');
        $tx2 = $this->buyStock->execute($this->userA, $this->apple, '2.00000000', '110.00000000', '2026-01-10');
        $tx3 = $this->buyStock->execute($this->userA, $this->apple, '3.00000000', '120.00000000', '2026-01-15');

        $recent = $this->dashboardService->getRecentTransactions($this->userA);
        $this->assertSame($tx3->id, $recent->first()->id);
        $this->assertSame($tx1->id, $recent->last()->id);
    }

    /**
     * 14. BUY transaction display in recent transactions table.
     */
    public function test_buy_transaction_display(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '150.00000000', '2026-01-10', 'First purchase');

        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertSee('BUY');
        $response->assertSee('10.0000');
        $response->assertSee('150.0000');
        $response->assertSee('1,500.00');
        $response->assertSee('First purchase');
    }

    /**
     * 15. SELL transaction display in recent transactions table.
     */
    public function test_sell_transaction_display(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '100.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '5.00000000', '125.00000000', '2026-01-15', 'Take partial profit');

        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertSee('SELL');
        $response->assertSee('5.0000');
        $response->assertSee('125.0000');
        $response->assertSee('625.00');
        $response->assertSee('Take partial profit');
    }

    /**
     * 16. SELL realized profit display.
     */
    public function test_sell_realized_profit_display(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        // Profit = 4 * (130 - 100) = 120.00
        $this->sellStock->execute($this->userA, $this->apple, '4.00000000', '130.00000000', '2026-01-15');

        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertSee('+120.00');
    }

    /**
     * 17. Current holdings only displays active positions.
     */
    public function test_current_holdings_only(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '5.00000000', '200.00000000', '2026-01-12');

        $holdings = $this->dashboardService->getCurrentHoldings($this->userA);

        $this->assertCount(2, $holdings);
        $symbols = $holdings->pluck('company.symbol')->all();
        $this->assertContains('AAPL', $symbols);
        $this->assertContains('MSFT', $symbols);
    }

    /**
     * 18. Zero-quantity positions excluded from current holdings.
     */
    public function test_zero_quantity_positions_excluded(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '10.00000000', '110.00000000', '2026-01-15');

        $holdings = $this->dashboardService->getCurrentHoldings($this->userA);
        $this->assertCount(0, $holdings);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('No active holdings in your portfolio');
    }

    /**
     * 19. Holdings WAC is calculated accurately.
     */
    public function test_holdings_wac(): void
    {
        // 10 @ 100 + 10 @ 200 = 20 @ 150
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '200.00000000', '2026-01-12');

        $holdings = $this->dashboardService->getCurrentHoldings($this->userA);
        $this->assertSame('150.00000000', (string) $holdings->first()->average_price);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('150.0000');
    }

    /**
     * 20. Holdings inventory cost is calculated accurately.
     */
    public function test_holdings_inventory_cost(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-01-10');

        $holdings = $this->dashboardService->getCurrentHoldings($this->userA);
        $this->assertSame('3000.00000000', (string) $holdings->first()->inventory_amount);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('3,000.00');
    }

    /**
     * 21. Empty portfolio state.
     */
    public function test_empty_portfolio_state(): void
    {
        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('0.00');
        $response->assertSee('0.0000');
    }

    /**
     * 22. Empty transaction state.
     */
    public function test_empty_transaction_state(): void
    {
        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertSee('No transactions recorded yet');
        $response->assertSee('Record First BUY Transaction');
    }

    /**
     * 23. Empty holdings state.
     */
    public function test_empty_holdings_state(): void
    {
        $response = $this->actingAs($this->userA)->get(route('dashboard'));

        $response->assertSee('No active holdings in your portfolio');
        $response->assertSee('Browse Companies &amp; Trade', false);
    }

    /**
     * 24. Current-month activity summary.
     */
    public function test_current_month_activity(): void
    {
        $currentMonth = Carbon::now()->format('Y-m');
        $dateBuy = "{$currentMonth}-02";
        $dateSell = "{$currentMonth}-05";

        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', $dateBuy);
        $this->sellStock->execute($this->userA, $this->apple, '4.00000000', '125.00000000', $dateSell);

        $activity = $this->dashboardService->getCurrentMonthActivity($this->userA);

        $this->assertSame(1, $activity['buy_count']);
        $this->assertSame(1, $activity['sell_count']);
        $this->assertSame(2, $activity['total_count']);
        $this->assertSame('100.00000000', $activity['realized_profit']);
        $this->assertSame('1000.00000000', $activity['buy_amount']);
        $this->assertSame('500.00000000', $activity['sell_amount']);

        $response = $this->actingAs($this->userA)->get(route('dashboard'));
        $response->assertSee('Recent Activity');
        $response->assertSee('+100.00');
    }

    /**
     * 25. Monthly realized P/L chart data.
     */
    public function test_monthly_realized_pl_chart_data(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '5.00000000', '130.00000000', '2026-01-15');

        $chart = $this->dashboardService->getMonthlyRealizedProfitChart($this->userA);

        $this->assertTrue($chart['has_data']);
        $this->assertSame(['January 2026'], $chart['labels']);
        $this->assertEquals([150.0], $chart['datasets'][0]['data']);
        $this->assertSame('150.00000000', $chart['total_realized_profit']);
    }

    /**
     * 26. Positive realized P/L.
     */
    public function test_positive_realized_pl(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '50.00000000', '2026-02-01');
        $this->sellStock->execute($this->userA, $this->apple, '10.00000000', '80.00000000', '2026-02-10');

        $chart = $this->dashboardService->getMonthlyRealizedProfitChart($this->userA);

        $this->assertSame('300.00000000', $chart['total_realized_profit']);
        $this->assertSame('#10b981', $chart['datasets'][0]['backgroundColor'][0]);
    }

    /**
     * 27. Negative realized P/L.
     */
    public function test_negative_realized_pl(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '100.00000000', '2026-02-01');
        $this->sellStock->execute($this->userA, $this->apple, '10.00000000', '70.00000000', '2026-02-10');

        $chart = $this->dashboardService->getMonthlyRealizedProfitChart($this->userA);

        $this->assertSame('-300.00000000', $chart['total_realized_profit']);
        $this->assertSame('#f43f5e', $chart['datasets'][0]['backgroundColor'][0]);
    }

    /**
     * 28. Multiple months grouped correctly.
     */
    public function test_multiple_months_grouped_correctly(): void
    {
        // Jan 2026
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '10.00000000', '120.00000000', '2026-01-15');
        // Feb 2026
        $this->sellStock->execute($this->userA, $this->apple, '10.00000000', '130.00000000', '2026-02-15');

        $chart = $this->dashboardService->getMonthlyRealizedProfitChart($this->userA);

        $this->assertSame(['January 2026', 'February 2026'], $chart['labels']);
        $this->assertEquals([200.0, 300.0], $chart['datasets'][0]['data']);
    }

    /**
     * 29. User A cannot see User B's dashboard data.
     */
    public function test_user_a_cannot_see_user_b_dashboard_data(): void
    {
        $this->buyStock->execute($this->userB, $this->pfizer, '500.00000000', '50.00000000', '2026-01-05');
        $this->sellStock->execute($this->userB, $this->pfizer, '100.00000000', '80.00000000', '2026-01-15');

        // User A fetches their dashboard
        $responseA = $this->actingAs($this->userA)->get(route('dashboard'));

        $responseA->assertDontSee('PFE');
        $responseA->assertDontSee('Pfizer Inc.');
        $responseA->assertDontSee('+3,000.00');

        $summaryA = $this->dashboardService->getPortfolioSummary($this->userA);
        $this->assertSame('0.00000000', $summaryA['total_inventory_cost']);
        $this->assertSame('0.00000000', $summaryA['total_realized_profit']);
    }

    /**
     * 30. Existing transaction/accounting regressions remain unaffected.
     */
    public function test_existing_transaction_accounting_regressions_remain_unaffected(): void
    {
        // 1. First BUY: 10 @ 100 = 1000 cost, WAC 100
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-01');
        // 2. Second BUY: 10 @ 200 = 2000 cost, total 3000 cost, 20 qty, WAC 150
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '200.00000000', '2026-01-05');
        // 3. SELL: 5 @ 180 = 900 revenue, cost = 5 * 150 = 750, profit = +150
        $this->sellStock->execute($this->userA, $this->apple, '5.00000000', '180.00000000', '2026-01-10');

        $position = StockPosition::query()
            ->where('user_id', $this->userA->id)
            ->where('company_id', $this->apple->id)
            ->first();

        $this->assertNotNull($position);
        $this->assertSame('15.00000000', (string) $position->quantity);
        $this->assertSame('150.00000000', (string) $position->average_price);
        $this->assertSame('2250.00000000', (string) $position->inventory_amount);
        $this->assertSame('150.00000000', (string) $position->realized_profit);

        $summary = $this->dashboardService->getPortfolioSummary($this->userA);
        $this->assertSame('2250.00000000', $summary['total_inventory_cost']);
        $this->assertSame('150.00000000', $summary['total_realized_profit']);
    }
}
