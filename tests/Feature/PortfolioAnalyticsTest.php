<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\Portfolio\PortfolioAnalyticsService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Company $apple;

    private Company $microsoft;

    private Company $pfizer;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    private PortfolioAnalyticsService $analyticsService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create(['name' => 'Investor Alice', 'email' => 'alice@example.com']);
        $this->userB = User::factory()->user()->create(['name' => 'Investor Bob', 'email' => 'bob@example.com']);

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
        $this->analyticsService = app(PortfolioAnalyticsService::class);
    }

    /**
     * 1. Guest cannot access analytics.
     */
    public function test_guest_cannot_access_analytics(): void
    {
        $response = $this->get(route('analytics.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated user can access analytics.
     */
    public function test_authenticated_user_can_access_analytics(): void
    {
        $response = $this->actingAs($this->userA)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee('Portfolio Visual Analytics');
        $response->assertSee('Monthly Trading Activity');
        $response->assertSee('Monthly Realized Profit / Loss');
        $response->assertSee('Realized Profit / Loss by Company');
        $response->assertSee('Inventory Cost Basis by Company');
        $response->assertSee('Transaction Distribution');
    }

    /**
     * 3. Empty analytics page works.
     */
    public function test_empty_analytics_page_works(): void
    {
        $response = $this->actingAs($this->userA)->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee('No monthly transaction activity found');
        $response->assertSee('No monthly realized profit records found');
        $response->assertSee('No closed positions for company profit breakdown');
        $response->assertSee('No active stock positions in inventory');

        $metrics = $this->analyticsService->getSummaryMetrics($this->userA);
        $this->assertSame(0, $metrics['total_buy_transactions']);
        $this->assertSame(0, $metrics['total_sell_transactions']);
        $this->assertSame('0.00000000', $metrics['total_realized_profit']);
        $this->assertSame('0.00000000', $metrics['total_inventory_cost']);
        $this->assertSame(0, $metrics['active_positions_count']);
        $this->assertSame(0, $metrics['companies_held_count']);
    }

    /**
     * 4. Summary metrics are correct.
     */
    public function test_summary_metrics_are_correct(): void
    {
        // Buy 100 Apple @ 150.00 = 15,000.00
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        // Buy 50 Microsoft @ 200.00 = 10,000.00
        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '200.00000000', '2026-01-12');
        // Sell 30 Apple @ 180.00 => Profit = 30 * 30 = 900.00
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '180.00000000', '2026-01-20');

        $metrics = $this->analyticsService->getSummaryMetrics($this->userA);

        $this->assertSame(2, $metrics['total_buy_transactions']);
        $this->assertSame(1, $metrics['total_sell_transactions']);
        $this->assertSame('900.00000000', $metrics['total_realized_profit']);
        // Remaining Apple: 70 @ 150 = 10,500.00; Microsoft: 50 @ 200 = 10,000.00 => Total = 20,500.00
        $this->assertSame('20500.00000000', $metrics['total_inventory_cost']);
        $this->assertSame(2, $metrics['active_positions_count']);
        $this->assertSame(2, $metrics['companies_held_count']);
    }

    /**
     * 5. BUY transaction count is correct.
     */
    public function test_buy_transaction_count_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-05');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '110.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '15.00000000', '200.00000000', '2026-01-15');

        $metrics = $this->analyticsService->getSummaryMetrics($this->userA);
        $this->assertSame(3, $metrics['total_buy_transactions']);
    }

    /**
     * 6. SELL transaction count is correct.
     */
    public function test_sell_transaction_count_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '130.00000000', '2026-01-15');

        $metrics = $this->analyticsService->getSummaryMetrics($this->userA);
        $this->assertSame(2, $metrics['total_sell_transactions']);
    }

    /**
     * 7. Monthly BUY count is correct.
     */
    public function test_monthly_buy_count_is_correct(): void
    {
        // Jan 2026: 2 BUYs
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-05');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '110.00000000', '2026-01-15');
        // Feb 2026: 1 BUY
        $this->buyStock->execute($this->userA, $this->apple, '30.00000000', '120.00000000', '2026-02-10');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA);

        $this->assertSame(['January 2026', 'February 2026'], $chart['labels']);
        // Dataset 0 is BUY Orders
        $this->assertSame([2, 1], $chart['datasets'][0]['data']);
    }

    /**
     * 8. Monthly SELL count is correct.
     */
    public function test_monthly_sell_count_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-01-15');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '130.00000000', '2026-02-10');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA);

        // Dataset 1 is SELL Orders
        $this->assertSame([1, 1], $chart['datasets'][1]['data']);
    }

    /**
     * 9. Different years remain separate.
     */
    public function test_different_years_remain_separate(): void
    {
        // January 2025
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2025-01-15');
        // January 2026
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-01-15');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA);

        $this->assertSame(['January 2025', 'January 2026'], $chart['labels']);
        $this->assertCount(2, $chart['labels']);
        $this->assertSame([1, 1], $chart['datasets'][0]['data']);
    }

    /**
     * 10. Monthly realized profit is correct.
     */
    public function test_monthly_realized_profit_is_correct(): void
    {
        // Buy @ 100
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        // Jan 2026: Sell 20 @ 150 => Profit = 20 * 50 = +1000.00
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-01-15');
        // Feb 2026: Sell 30 @ 140 => Profit = 30 * 40 = +1200.00
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '140.00000000', '2026-02-15');

        $chart = $this->analyticsService->getMonthlyRealizedProfitChart($this->userA);

        $this->assertSame(['January 2026', 'February 2026'], $chart['labels']);
        $this->assertEquals([1000.0, 1200.0], $chart['datasets'][0]['data']);
        $this->assertSame('2200.00000000', $chart['total_realized_profit']);
    }

    /**
     * 11. Negative realized profit is represented correctly.
     */
    public function test_negative_realized_profit_is_represented_correctly(): void
    {
        // Buy @ 100
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        // Sell 50 @ 70 => Loss = 50 * (-30) = -1500.00
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '70.00000000', '2026-01-15');

        $chart = $this->analyticsService->getMonthlyRealizedProfitChart($this->userA);

        $this->assertSame(['January 2026'], $chart['labels']);
        $this->assertEquals([-1500.0], $chart['datasets'][0]['data']);
        $this->assertSame(['#f43f5e'], $chart['datasets'][0]['backgroundColor']); // Red/rose for negative
    }

    /**
     * 12. Company realized profit aggregation is correct.
     */
    public function test_company_realized_profit_aggregation_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '40.00000000', '150.00000000', '2026-01-10'); // +2000

        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '200.00000000', '2026-01-06');
        $this->sellStock->execute($this->userA, $this->microsoft, '20.00000000', '180.00000000', '2026-01-12'); // -400

        $chart = $this->analyticsService->getCompanyRealizedProfitChart($this->userA);

        $this->assertCount(2, $chart['labels']);
        $this->assertSame('AAPL', $chart['labels'][0]);
        $this->assertSame('MSFT', $chart['labels'][1]);
        $this->assertEquals(2000.0, $chart['datasets'][0]['data'][0]);
        $this->assertEquals(-400.0, $chart['datasets'][0]['data'][1]);
        $this->assertSame('#10b981', $chart['datasets'][0]['backgroundColor'][0]); // Green for AAPL
        $this->assertSame('#f43f5e', $chart['datasets'][0]['backgroundColor'][1]); // Red for MSFT
    }

    /**
     * 13. Current inventory cost basis aggregation is correct.
     */
    public function test_current_inventory_cost_basis_aggregation_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '125.00000000', '2026-01-05'); // 12,500.00
        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '200.00000000', '2026-01-06'); // 10,000.00

        $chart = $this->analyticsService->getCostBasisDistributionChart($this->userA);

        $this->assertSame(['AAPL', 'MSFT'], $chart['labels']);
        $this->assertEquals([12500.0, 10000.0], $chart['datasets'][0]['data']);
        $this->assertSame('22500.00000000', $chart['total_cost_basis']);
    }

    /**
     * 14. Zero-quantity positions are excluded from active cost-basis chart data.
     */
    public function test_zero_quantity_positions_are_excluded_from_active_cost_basis_chart_data(): void
    {
        // Buy Apple and fully liquidate
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '120.00000000', '2026-01-10');

        // Buy Microsoft and keep active
        $this->buyStock->execute($this->userA, $this->microsoft, '20.00000000', '200.00000000', '2026-01-12');

        $chart = $this->analyticsService->getCostBasisDistributionChart($this->userA);

        // Apple is liquidated (quantity = 0), so it MUST NOT be in the active cost basis chart
        $this->assertNotContains('AAPL', $chart['labels']);
        $this->assertContains('MSFT', $chart['labels']);
        $this->assertCount(1, $chart['labels']);
    }

    /**
     * 15. Transaction distribution is correct.
     */
    public function test_transaction_distribution_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-05');
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '110.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '130.00000000', '2026-01-15');

        $chart = $this->analyticsService->getTransactionDistributionChart($this->userA);

        $this->assertSame(2, $chart['buy_count']);
        $this->assertSame(1, $chart['sell_count']);
        $this->assertSame(3, $chart['total_count']);
        $this->assertSame([2, 1], $chart['datasets'][0]['data']);
    }

    /**
     * 16. Date-from filter works.
     */
    public function test_date_from_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-02-10');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA, [
            'date_from' => '2026-02-01',
        ]);

        $this->assertSame(['February 2026'], $chart['labels']);
        $this->assertSame([1], $chart['datasets'][0]['data']);
    }

    /**
     * 17. Date-to filter works.
     */
    public function test_date_to_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-02-10');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA, [
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(['January 2026'], $chart['labels']);
        $this->assertSame([1], $chart['datasets'][0]['data']);
    }

    /**
     * 18. Date boundaries are inclusive.
     */
    public function test_date_boundaries_are_inclusive(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-01');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '110.00000000', '2026-01-15');
        $this->buyStock->execute($this->userA, $this->apple, '30.00000000', '120.00000000', '2026-01-31');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA, [
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame([3], $chart['datasets'][0]['data']);
    }

    /**
     * 19. Company filter works.
     */
    public function test_company_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '30.00000000', '250.00000000', '2026-01-12');

        $chart = $this->analyticsService->getCostBasisDistributionChart($this->userA, [
            'company_id' => $this->apple->id,
        ]);

        $this->assertSame(['AAPL'], $chart['labels']);
        $this->assertCount(1, $chart['labels']);
    }

    /**
     * 20. Exchange filter works.
     */
    public function test_exchange_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');  // NASDAQ
        $this->buyStock->execute($this->userA, $this->pfizer, '100.00000000', '30.00000000', '2026-01-15'); // NYSE

        $chart = $this->analyticsService->getCostBasisDistributionChart($this->userA, [
            'exchange' => 'NYSE',
        ]);

        $this->assertSame(['PFE'], $chart['labels']);
        $this->assertCount(1, $chart['labels']);
    }

    /**
     * 21. Sector filter works.
     */
    public function test_sector_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');  // Technology
        $this->buyStock->execute($this->userA, $this->pfizer, '100.00000000', '30.00000000', '2026-01-15'); // Healthcare

        $chart = $this->analyticsService->getCostBasisDistributionChart($this->userA, [
            'sector' => 'Technology',
        ]);

        $this->assertSame(['AAPL'], $chart['labels']);
        $this->assertCount(1, $chart['labels']);
    }

    /**
     * 22. Transaction-type filter works.
     */
    public function test_transaction_type_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '170.00000000', '2026-01-15');

        $chart = $this->analyticsService->getTransactionDistributionChart($this->userA, [
            'type' => 'BUY',
        ]);

        $this->assertSame(1, $chart['buy_count']);
        $this->assertSame(0, $chart['sell_count']);
    }

    /**
     * 23. Combined filters work.
     */
    public function test_combined_filters_work(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '170.00000000', '2026-01-20');
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '180.00000000', '2026-02-10');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA, [
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'company_id' => $this->apple->id,
            'type' => 'SELL',
        ]);

        $this->assertSame(['January 2026'], $chart['labels']);
        $this->assertSame([0], $chart['datasets'][0]['data']); // 0 BUYs
        $this->assertSame([1], $chart['datasets'][1]['data']); // 1 SELL
    }

    /**
     * 24. User isolation works.
     */
    public function test_user_isolation_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userB, $this->microsoft, '50.00000000', '300.00000000', '2026-01-12');

        $userAChart = $this->analyticsService->getCostBasisDistributionChart($this->userA);
        $userBChart = $this->analyticsService->getCostBasisDistributionChart($this->userB);

        $this->assertSame(['AAPL'], $userAChart['labels']);
        $this->assertSame(['MSFT'], $userBChart['labels']);
    }

    /**
     * 25. Another user's data never appears.
     */
    public function test_another_users_data_never_appears(): void
    {
        // User B has transactions
        $this->buyStock->execute($this->userB, $this->pfizer, '1000.00000000', '50.00000000', '2026-01-10');

        // User A has nothing
        $response = $this->actingAs($this->userA)->get(route('analytics.index'));
        $response->assertOk();
        $response->assertDontSee('Pfizer Inc.');

        $costBasis = $this->analyticsService->getCostBasisDistributionChart($this->userA);
        $this->assertEmpty($costBasis['labels']);
    }

    /**
     * 26. BUY-only history works.
     */
    public function test_buy_only_history_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('analytics.index'));
        $response->assertOk();

        $realized = $this->analyticsService->getCompanyRealizedProfitChart($this->userA);
        $this->assertFalse($realized['has_data']);
        $this->assertEmpty($realized['labels']);

        $costBasis = $this->analyticsService->getCostBasisDistributionChart($this->userA);
        $this->assertTrue($costBasis['has_data']);
        $this->assertSame(['AAPL'], $costBasis['labels']);
    }

    /**
     * 27. SELL-only filter works.
     */
    public function test_sell_only_history_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '170.00000000', '2026-01-15');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA, [
            'type' => 'SELL',
        ]);

        $this->assertSame([0], $chart['datasets'][0]['data']); // 0 BUYs
        $this->assertSame([1], $chart['datasets'][1]['data']); // 1 SELL
    }

    /**
     * 28. Empty selected date range works.
     */
    public function test_empty_selected_date_range_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');

        $chart = $this->analyticsService->getMonthlyTradingActivityChart($this->userA, [
            'date_from' => '2029-01-01',
            'date_to' => '2029-01-31',
        ]);

        $this->assertFalse($chart['has_data']);
        $this->assertEmpty($chart['labels']);
    }

    /**
     * 29. Date validation rejects inverted date range.
     */
    public function test_date_validation_rejects_inverted_date_range(): void
    {
        $response = $this->actingAs($this->userA)->get(route('analytics.index', [
            'date_from' => '2026-05-01',
            'date_to' => '2026-01-01',
        ]));

        $response->assertSessionHasErrors('date_to');
    }
}
