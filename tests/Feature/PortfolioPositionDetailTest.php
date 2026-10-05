<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Portfolio\PortfolioService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPositionDetailTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private User $admin;

    private Company $company;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    private PortfolioService $portfolioService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create(['name' => 'Investor Alice']);
        $this->userB = User::factory()->user()->create(['name' => 'Investor Bob']);
        $this->admin = User::factory()->admin()->create(['name' => 'Admin User']);

        $this->company = Company::factory()->create([
            'symbol' => 'AAPL',
            'name' => 'Apple Inc.',
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
     * 1. Guest redirected to login.
     */
    public function test_guest_redirected_to_login(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->get(route('portfolio.positions.show', $pos));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. User can view own position.
     */
    public function test_user_can_view_own_position(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        $response->assertSee('Position Detail');
    }

    /**
     * 3. Position detail displays company information.
     */
    public function test_position_detail_displays_company_information(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('Apple Inc.');
        $response->assertSee('AAPL');
        $response->assertSee('NASDAQ');
        $response->assertSee('Technology');
        $response->assertSee('USD');
        $response->assertSee('Active');
    }

    /**
     * 4. Current quantity displayed correctly.
     */
    public function test_current_quantity_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '30', '60.00', '2026-10-02');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('70.0000');
    }

    /**
     * 5. Current WAC displayed correctly.
     */
    public function test_current_wac_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->company, '50', '60.00', '2026-10-02');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('53.3333');
    }

    /**
     * 6. Inventory cost basis displayed correctly.
     */
    public function test_inventory_cost_basis_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('Inventory Cost Basis');
        $response->assertSee('5,000.00');
    }

    /**
     * 7. Realized profit displayed correctly.
     */
    public function test_realized_profit_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02'); // Profit 800
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('800.00');
    }

    /**
     * 8. Holding status displayed correctly.
     */
    public function test_holding_status_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('Holding');
    }

    /**
     * 9. Zero status displayed correctly.
     */
    public function test_zero_status_displayed_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '100', '60.00', '2026-10-02');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('Zero');
    }

    /**
     * 10. BUY transactions appear in ledger.
     */
    public function test_buy_transactions_appear(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01', 'Initial purchase');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('BUY');
        $response->assertSee('5,000.00');
        $response->assertSee('Initial purchase');
    }

    /**
     * 11. SELL transactions appear in ledger and realized profit history.
     */
    public function test_sell_transactions_appear(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02', 'Sold 40 shares');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('SELL');
        $response->assertSee('2,800.00');
        $response->assertSee('2,000.00');
        $response->assertSee('800.00');
        $response->assertSee('Sold 40 shares');
    }

    /**
     * 12. Transactions are ordered newest first.
     */
    public function test_transactions_are_ordered_newest_first(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01', 'First order');
        $this->buyStock->execute($this->userA, $this->company, '50', '60.00', '2026-10-05', 'Second order');
        $pos = StockPosition::first();

        $transactions = $this->portfolioService->getPositionTransactions($pos);

        $this->assertSame('Second order', $transactions->first()->notes);
        $this->assertSame('First order', $transactions->last()->notes);
    }

    /**
     * 13. Transaction values are displayed from stored ledger values.
     */
    public function test_transaction_values_are_displayed_from_stored_ledger_values(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02');
        $pos = StockPosition::first();

        $sellTx = StockTransaction::where('type', 'SELL')->first();
        $this->assertSame('2800.00000000', (string) $sellTx->amount);
        $this->assertSame('2000.00000000', (string) $sellTx->cost);
        $this->assertSame('800.00000000', (string) $sellTx->realized_profit);

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('2,800.00');
        $response->assertSee('2,000.00');
        $response->assertSee('800.00');
    }

    /**
     * 14. BUY summary totals are correct.
     */
    public function test_buy_summary_totals_are_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01'); // 100 @ 50 = 5000
        $this->buyStock->execute($this->userA, $this->company, '50', '60.00', '2026-10-02');  // 50 @ 60 = 3000
        $pos = StockPosition::first();

        $summary = $this->portfolioService->getPositionTransactionSummary($pos);

        $this->assertSame('150.00000000', $summary['total_buy_quantity']);
        $this->assertSame('8000.00000000', $summary['total_buy_amount']);
        $this->assertSame(2, $summary['buy_transactions_count']);
    }

    /**
     * 15. SELL summary totals are correct.
     */
    public function test_sell_summary_totals_are_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02'); // Revenue 2800, Cost 2000
        $this->sellStock->execute($this->userA, $this->company, '10', '60.00', '2026-10-03'); // Revenue 600, Cost 500
        $pos = StockPosition::first();

        $summary = $this->portfolioService->getPositionTransactionSummary($pos);

        $this->assertSame('50.00000000', $summary['total_sell_quantity']);
        $this->assertSame('3400.00000000', $summary['total_sell_amount']);
        $this->assertSame('2500.00000000', $summary['total_sell_cost']);
        $this->assertSame(2, $summary['sell_transactions_count']);
    }

    /**
     * 16. Total realized profit is correct.
     */
    public function test_total_realized_profit_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02'); // +800
        $this->sellStock->execute($this->userA, $this->company, '10', '40.00', '2026-10-03'); // -100
        $pos = StockPosition::first();

        $summary = $this->portfolioService->getPositionTransactionSummary($pos);

        $this->assertSame('700.00000000', $summary['total_realized_profit']);
    }

    /**
     * 17. SELL profit history is correct.
     */
    public function test_sell_profit_history_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02', 'Harvesting gains');
        $pos = StockPosition::first();

        $sellTxs = $this->portfolioService->getPositionSellTransactions($pos);

        $this->assertCount(1, $sellTxs);
        $this->assertSame('800.00000000', (string) $sellTxs->first()->realized_profit);
        $this->assertSame('Harvesting gains', $sellTxs->first()->notes);
    }

    /**
     * 18. Multiple BUY transactions work.
     */
    public function test_multiple_buy_transactions_work(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '10', '100', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->company, '20', '110', '2026-10-02');
        $this->buyStock->execute($this->userA, $this->company, '30', '120', '2026-10-03');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        $this->assertSame(3, StockTransaction::where('type', 'BUY')->count());
    }

    /**
     * 19. Multiple SELL transactions work.
     */
    public function test_multiple_sell_transactions_work(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '20', '60', '2026-10-02');
        $this->sellStock->execute($this->userA, $this->company, '30', '70', '2026-10-03');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        $this->assertSame(2, StockTransaction::where('type', 'SELL')->count());
    }

    /**
     * 20. Fully liquidated position remains accessible.
     */
    public function test_fully_liquidated_position_remains_accessible(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '100', '60', '2026-10-02');
        $pos = StockPosition::first();

        $this->assertSame('0.00000000', (string) $pos->quantity);

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        $response->assertSee('Zero');
        $response->assertSee('0.0000');
    }

    /**
     * 21. Zero-quantity position retains realized profit.
     */
    public function test_zero_quantity_position_retains_realized_profit(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '100', '60', '2026-10-02'); // Profit 1000
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertSee('1,000.00');
    }

    /**
     * 22. User isolation works.
     */
    public function test_user_isolation_works(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $posA = StockPosition::where('user_id', $this->userA->id)->first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $posA));

        $response->assertStatus(200);
    }

    /**
     * 23. Another user's position returns 403.
     */
    public function test_another_users_position_returns_403(): void
    {
        $this->buyStock->execute($this->userB, $this->company, '100', '50', '2026-10-01');
        $posB = StockPosition::where('user_id', $this->userB->id)->first();

        // Standard User A accessing User B's position
        $responseA = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $posB));
        $responseA->assertStatus(403);

        // Admin accessing User B's personal portfolio position
        $responseAdmin = $this->actingAs($this->admin)->get(route('portfolio.positions.show', $posB));
        $responseAdmin->assertStatus(403);
    }

    /**
     * 24. Another user's transactions never appear.
     */
    public function test_another_users_transactions_never_appear(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01', 'Alice Secret Trade');
        $this->buyStock->execute($this->userB, $this->company, '50', '100', '2026-10-02', 'Bob Secret Trade');

        $posA = StockPosition::where('user_id', $this->userA->id)->first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $posA));

        $response->assertSee('Alice Secret Trade');
        $response->assertDontSee('Bob Secret Trade');
    }

    /**
     * 25. SELL link is unavailable when quantity is zero.
     */
    public function test_sell_link_is_unavailable_when_quantity_is_zero(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $this->sellStock->execute($this->userA, $this->company, '100', '60', '2026-10-02'); // Q=0
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        // SELL button is disabled in the UI
        $response->assertSee('disabled');
        $response->assertDontSee(route('stocks.sell.create', $this->company));
    }

    /**
     * 26. BUY link remains available according to existing application behavior.
     */
    public function test_buy_link_remains_available(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        $response->assertSee(route('stocks.buy.create', $this->company));
        $response->assertSee('BUY Stock');
    }

    /**
     * 27. No market value or unrealized P&L is displayed.
     */
    public function test_no_market_value_or_unrealized_pl_is_displayed(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $pos = StockPosition::first();

        $response = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $pos));

        $response->assertStatus(200);
        $response->assertSee('Inventory Cost Basis');
        $response->assertDontSee('Current Market Value');
        $response->assertDontSee('Unrealized Profit');
        $response->assertDontSee('Unrealized Loss');
        $response->assertDontSee('Market Return');
    }
}
