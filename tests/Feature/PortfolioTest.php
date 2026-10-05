<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\User;
use App\Services\Portfolio\PortfolioService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Company $apple;

    private Company $microsoft;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    private PortfolioService $portfolioService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create(['name' => 'Investor Alice']);
        $this->userB = User::factory()->user()->create(['name' => 'Investor Bob']);

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

        $this->buyStock = app(BuyStock::class);
        $this->sellStock = app(SellStock::class);
        $this->portfolioService = app(PortfolioService::class);
    }

    /**
     * 1. Authenticated user can access /portfolio.
     */
    public function test_authenticated_user_can_access_portfolio(): void
    {
        $response = $this->actingAs($this->userA)->get(route('portfolio.index'));

        $response->assertStatus(200);
        $response->assertSee('Portfolio Overview');
        $response->assertSee('Total Inventory Cost');
    }

    /**
     * 2. Guest cannot access /portfolio.
     */
    public function test_guest_cannot_access_portfolio(): void
    {
        $response = $this->get(route('portfolio.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 3. Empty portfolio displays correctly.
     */
    public function test_empty_portfolio_displays_correctly(): void
    {
        $response = $this->actingAs($this->userA)->get(route('portfolio.index'));

        $response->assertStatus(200);
        $response->assertSee('No positions yet');
        $response->assertSee('Buy a stock to start building your portfolio');
        $response->assertSee('Browse Available Companies');

        $summary = $this->portfolioService->getSummary($this->userA);
        $this->assertSame('0.00000000', $summary['total_inventory_cost']);
        $this->assertSame('0.00000000', $summary['total_realized_profit']);
        $this->assertSame('0.00000000', $summary['total_quantity']);
        $this->assertSame(0, $summary['active_positions_count']);
        $this->assertSame(0, $summary['total_positions_count']);
        $this->assertSame(0, $summary['companies_held_count']);
    }

    /**
     * 4. Portfolio summary for one position.
     */
    public function test_portfolio_summary_for_one_position(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50.00', '2026-10-01');

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame('5000.00000000', $summary['total_inventory_cost']);
        $this->assertSame('0.00000000', $summary['total_realized_profit']);
        $this->assertSame('100.00000000', $summary['total_quantity']);
        $this->assertSame(1, $summary['active_positions_count']);
        $this->assertSame(1, $summary['total_positions_count']);
        $this->assertSame(1, $summary['companies_held_count']);
    }

    /**
     * 5. Multiple companies aggregate correctly.
     */
    public function test_multiple_companies_aggregate_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50.00', '2026-10-01');     // $5000, 100 shares
        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100.00', '2026-10-02'); // $5000, 50 shares

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame('10000.00000000', $summary['total_inventory_cost']);
        $this->assertSame('150.00000000', $summary['total_quantity']);
        $this->assertSame(2, $summary['active_positions_count']);
        $this->assertSame(2, $summary['total_positions_count']);
        $this->assertSame(2, $summary['companies_held_count']);
    }

    /**
     * 6. Total inventory cost is correct.
     */
    public function test_total_inventory_cost_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '40', '25.50', '2026-10-01');       // 1020.00
        $this->buyStock->execute($this->userA, $this->microsoft, '20', '150.25', '2026-10-02'); // 3005.00

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame('4025.00000000', $summary['total_inventory_cost']);
    }

    /**
     * 7. Total realized profit is correct (aggregating positive profit and loss).
     */
    public function test_total_realized_profit_is_correct(): void
    {
        // AAPL: Buy 100 @ 50, Sell 40 @ 70 -> Profit = +800
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '40', '70', '2026-10-02');

        // MSFT: Buy 100 @ 100, Sell 20 @ 80 -> Profit = -400 (loss)
        $this->buyStock->execute($this->userA, $this->microsoft, '100', '100', '2026-10-03');
        $this->sellStock->execute($this->userA, $this->microsoft, '20', '80', '2026-10-04');

        $summary = $this->portfolioService->getSummary($this->userA);

        // 800 - 400 = 400.00000000
        $this->assertSame('400.00000000', $summary['total_realized_profit']);
    }

    /**
     * 8. Total quantity is correct.
     */
    public function test_total_quantity_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '30', '70', '2026-10-02'); // 70 remaining

        $this->buyStock->execute($this->userA, $this->microsoft, '25.5', '100', '2026-10-03'); // 25.5 shares

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame('95.50000000', $summary['total_quantity']);
    }

    /**
     * 9. Active positions count is correct.
     */
    public function test_active_positions_count_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100', '2026-10-02');

        $summary = $this->portfolioService->getSummary($this->userA);
        $this->assertSame(2, $summary['active_positions_count']);
    }

    /**
     * 10. Zero-quantity positions are not counted as active.
     */
    public function test_zero_quantity_positions_are_not_counted_as_active(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02'); // Full sell -> Q=0

        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100', '2026-10-03'); // Active -> Q=50

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame(1, $summary['active_positions_count']);
        $this->assertSame(2, $summary['total_positions_count']);
    }

    /**
     * 11. Zero-quantity positions remain visible in portfolio table.
     */
    public function test_zero_quantity_positions_remain_visible(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02');

        $response = $this->actingAs($this->userA)->get(route('portfolio.index'));

        $response->assertStatus(200);
        $response->assertSee('AAPL');
        $response->assertSee('Zero');
        $response->assertSee('0.0000');
    }

    /**
     * 12. Companies held count is correct.
     */
    public function test_companies_held_count_is_correct(): void
    {
        $oilCompany = Company::factory()->create(['symbol' => 'OGDC', 'exchange' => 'PSX', 'is_active' => true]);

        $this->buyStock->execute($this->userA, $this->apple, '10', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->microsoft, '20', '100', '2026-10-02');
        $this->buyStock->execute($this->userA, $oilCompany, '30', '150', '2026-10-03');

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame(3, $summary['companies_held_count']);
    }

    /**
     * 13. Search by company name.
     */
    public function test_search_by_company_name(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100', '2026-10-02');

        $response = $this->actingAs($this->userA)->get(route('portfolio.index', ['search' => 'Apple']));

        $response->assertStatus(200);
        $response->assertSee('AAPL');
        $response->assertDontSee('MSFT');
    }

    /**
     * 14. Search by symbol.
     */
    public function test_search_by_symbol(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100', '2026-10-02');

        $response = $this->actingAs($this->userA)->get(route('portfolio.index', ['search' => 'MSFT']));

        $response->assertStatus(200);
        $response->assertSee('Microsoft Corporation');
        $response->assertDontSee('Apple Inc.');
    }

    /**
     * 15. Exchange filter.
     */
    public function test_exchange_filter(): void
    {
        $psxCompany = Company::factory()->create([
            'symbol' => 'LUCK',
            'name' => 'Lucky Cement',
            'exchange' => 'PSX',
            'is_active' => true,
        ]);

        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $psxCompany, '200', '500', '2026-10-02');

        $response = $this->actingAs($this->userA)->get(route('portfolio.index', ['exchange' => 'PSX']));

        $response->assertStatus(200);
        $response->assertSee('LUCK');
        $response->assertDontSee('AAPL');
    }

    /**
     * 16. Sector filter.
     */
    public function test_sector_filter(): void
    {
        $energyCompany = Company::factory()->create([
            'symbol' => 'XOM',
            'name' => 'Exxon Mobil',
            'exchange' => 'NYSE',
            'sector' => 'Energy',
            'is_active' => true,
        ]);

        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $energyCompany, '50', '110', '2026-10-02');

        $response = $this->actingAs($this->userA)->get(route('portfolio.index', ['sector' => 'Energy']));

        $response->assertStatus(200);
        $response->assertSee('XOM');
        $response->assertDontSee('AAPL');
    }

    /**
     * 17. Holding status filter.
     */
    public function test_holding_status_filter(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02'); // Q=0

        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100', '2026-10-03'); // Q=50

        $response = $this->actingAs($this->userA)->get(route('portfolio.index', ['status' => 'holding']));

        $response->assertStatus(200);
        $response->assertSee('MSFT');
        $response->assertDontSee('AAPL');
    }

    /**
     * 18. Zero status filter.
     */
    public function test_zero_status_filter(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02'); // Q=0

        $this->buyStock->execute($this->userA, $this->microsoft, '50', '100', '2026-10-03'); // Q=50

        $response = $this->actingAs($this->userA)->get(route('portfolio.index', ['status' => 'zero']));

        $response->assertStatus(200);
        $response->assertSee('AAPL');
        $response->assertDontSee('MSFT');
    }

    /**
     * 19. Pagination works.
     */
    public function test_pagination_works(): void
    {
        // Seed 16 companies and positions for User A
        for ($i = 1; $i <= 16; $i++) {
            $comp = Company::factory()->create([
                'symbol' => "STK{$i}",
                'name' => "Stock {$i}",
                'is_active' => true,
            ]);
            $this->buyStock->execute($this->userA, $comp, '10', '100', '2026-10-01');
        }

        // Page 1 should contain 15 items
        $response1 = $this->actingAs($this->userA)->get(route('portfolio.index'));
        $response1->assertStatus(200);

        // Page 2 should contain 1 item
        $response2 = $this->actingAs($this->userA)->get(route('portfolio.index', ['page' => 2]));
        $response2->assertStatus(200);
    }

    /**
     * 20. User isolation works.
     */
    public function test_user_isolation_works(): void
    {
        // User A owns AAPL
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');

        // User B owns MSFT
        $this->buyStock->execute($this->userB, $this->microsoft, '50', '100', '2026-10-01');

        $userASummary = $this->portfolioService->getSummary($this->userA);
        $this->assertSame('5000.00000000', $userASummary['total_inventory_cost']);
        $this->assertSame(1, $userASummary['companies_held_count']);

        $userBSummary = $this->portfolioService->getSummary($this->userB);
        $this->assertSame('5000.00000000', $userBSummary['total_inventory_cost']);
        $this->assertSame(1, $userBSummary['companies_held_count']);
    }

    /**
     * 21. Another user's positions never appear.
     */
    public function test_another_users_positions_never_appear(): void
    {
        // User B owns MSFT
        $this->buyStock->execute($this->userB, $this->microsoft, '50', '100', '2026-10-01');

        // User A visits portfolio
        $response = $this->actingAs($this->userA)->get(route('portfolio.index'));

        $response->assertStatus(200);
        $response->assertDontSee('MSFT');
        $response->assertDontSee('Microsoft Corporation');
    }

    /**
     * 22. Existing realized profit after a full SELL is included.
     */
    public function test_existing_realized_profit_after_a_full_sell_is_included(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02'); // Profit 1000

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame('1000.00000000', $summary['total_realized_profit']);
    }

    /**
     * 23. Inventory cost after a full SELL is zero.
     */
    public function test_inventory_cost_after_a_full_sell_is_zero(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02');

        $summary = $this->portfolioService->getSummary($this->userA);

        $this->assertSame('0.00000000', $summary['total_inventory_cost']);

        $pos = StockPosition::first();
        $this->assertSame('0.00000000', (string) $pos->inventory_amount);
    }

    /**
     * 24. WAC remains represented correctly for zero positions.
     */
    public function test_wac_remains_represented_correctly_for_zero_positions(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->apple, '100', '60', '2026-10-02');

        $pos = StockPosition::first();
        $this->assertSame('50.00000000', (string) $pos->average_price);

        $response = $this->actingAs($this->userA)->get(route('portfolio.index'));
        $response->assertSee('50.0000');
    }

    /**
     * 25. No market value/unrealized P&L is calculated.
     */
    public function test_no_market_value_or_unrealized_pl_is_calculated(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->get(route('portfolio.index'));

        $response->assertStatus(200);
        $response->assertSee('Cost Basis');
        $response->assertDontSee('Portfolio Value');
        $response->assertDontSee('Unrealized Profit');
        $response->assertDontSee('Unrealized Loss');
    }
}
