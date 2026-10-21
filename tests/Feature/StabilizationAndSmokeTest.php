<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StabilizationAndSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private User $admin;

    private Company $apple;

    private Company $microsoft;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create([
            'name' => 'Investor Alice',
            'email' => 'alice.audit@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->userB = User::factory()->user()->create([
            'name' => 'Investor Bob',
            'email' => 'bob.audit@example.com',
            'password' => bcrypt('Password123!'),
        ]);

        $this->admin = User::factory()->admin()->create([
            'name' => 'Admin Alex',
            'email' => 'alex.admin@example.com',
            'password' => bcrypt('Password123!'),
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

        $this->buyStock = app(BuyStock::class);
        $this->sellStock = app(SellStock::class);
    }

    /**
     * Complete End-to-End Workflow & Smoke Test (Phase 6 Requirement 22).
     */
    public function test_complete_end_to_end_portfolio_lifecycle_and_user_isolation_smoke_test(): void
    {
        // 1. Login as User A
        $loginResponse = $this->post('/login', [
            'email' => 'alice.audit@example.com',
            'password' => 'Password123!',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->userA);

        // 2. Dashboard initially empty
        $dashboardResponse = $this->actingAs($this->userA)->get(route('dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Welcome back, Investor Alice');
        $dashboardResponse->assertSee('No active holdings in your portfolio');
        $dashboardResponse->assertSee('No transactions recorded yet');

        // 3. Browse Companies
        $companiesResponse = $this->actingAs($this->userA)->get(route('companies.index'));
        $companiesResponse->assertOk();
        $companiesResponse->assertSee('AAPL');
        $companiesResponse->assertSee('Apple Inc.');

        // 4. Select company workspace
        $workspaceResponse = $this->actingAs($this->userA)->get(route('stocks.show', $this->apple));
        $workspaceResponse->assertOk();
        $workspaceResponse->assertSee('Apple Inc.');

        // 5. First BUY: 100 shares @ $150.00 on 2026-01-10
        $buy1Response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->apple), [
            'company_id' => $this->apple->id,
            'quantity' => '100.0000',
            'price' => '150.0000',
            'transaction_date' => '2026-01-10',
            'notes' => 'Initial lot purchase',
        ]);
        $buy1Response->assertRedirect(route('stocks.show', $this->apple));

        // 6. Verify Position after First BUY
        $position = StockPosition::query()
            ->where('user_id', $this->userA->id)
            ->where('company_id', $this->apple->id)
            ->first();

        $this->assertNotNull($position);
        $this->assertSame('100.00000000', (string) $position->quantity);
        $this->assertSame('150.00000000', (string) $position->average_price);
        $this->assertSame('15000.00000000', (string) $position->inventory_amount);
        $this->assertSame('0.00000000', (string) $position->realized_profit);

        // 7. Second BUY at different price: 50 shares @ $180.00 on 2026-01-15
        // Cost = 15000 + (50 * 180 = 9000) = 24000. Qty = 150. WAC = 24000 / 150 = 160.00
        $buy2Response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->apple), [
            'company_id' => $this->apple->id,
            'quantity' => '50.0000',
            'price' => '180.0000',
            'transaction_date' => '2026-01-15',
            'notes' => 'Second lot accumulation',
        ]);
        $buy2Response->assertRedirect(route('stocks.show', $this->apple));

        // 8. Verify WAC recalculation
        $position->refresh();
        $this->assertSame('150.00000000', (string) $position->quantity);
        $this->assertSame('160.00000000', (string) $position->average_price);
        $this->assertSame('24000.00000000', (string) $position->inventory_amount);

        // 9. SELL part of position: 30 shares @ $200.00 on 2026-01-20
        // Revenue = 30 * 200 = 6000. Cost of sold = 30 * 160 = 4800. Realized Profit = +1200.00.
        // Remaining Qty = 120. Remaining Inv = 120 * 160 = 19200. WAC = 160.00 (UNCHANGED).
        $sellResponse = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->apple), [
            'company_id' => $this->apple->id,
            'quantity' => '30.0000',
            'price' => '200.0000',
            'transaction_date' => '2026-01-20',
            'notes' => 'First partial profit exit',
        ]);
        $sellResponse->assertRedirect(route('stocks.show', $this->apple));

        // 10 & 11 & 12. Verify SELL accounting invariants
        $position->refresh();
        $this->assertSame('120.00000000', (string) $position->quantity);
        $this->assertSame('160.00000000', (string) $position->average_price); // WAC UNCHANGED
        $this->assertSame('19200.00000000', (string) $position->inventory_amount);
        $this->assertSame('1200.00000000', (string) $position->realized_profit);

        // 13. Open Portfolio Index
        $portfolioResponse = $this->actingAs($this->userA)->get(route('portfolio.index'));
        $portfolioResponse->assertOk();
        $portfolioResponse->assertSee('19,200.00'); // Total Inventory Cost
        $portfolioResponse->assertSee('1,200.00'); // Realized Profit
        $portfolioResponse->assertSee('120.0000'); // Remaining Qty

        // 14. Open Position Detail Page
        $positionDetailResponse = $this->actingAs($this->userA)->get(route('portfolio.positions.show', $position));
        $positionDetailResponse->assertOk();
        $positionDetailResponse->assertSee('Apple Inc.');
        $positionDetailResponse->assertSee('160.0000'); // WAC
        $positionDetailResponse->assertSee('1,200.00'); // Realized Profit
        $positionDetailResponse->assertSee('First partial profit exit');

        // 15. Open Reports Page
        $reportsResponse = $this->actingAs($this->userA)->get(route('reports.index'));
        $reportsResponse->assertOk();
        $reportsResponse->assertSee('Historical Financial Reports');
        $reportsResponse->assertSee('19,200.00');

        // 16. Open Analytics Page
        $analyticsResponse = $this->actingAs($this->userA)->get(route('analytics.index'));
        $analyticsResponse->assertOk();
        $analyticsResponse->assertSee('Portfolio Visual Analytics');

        // 17. Export Excel
        $excelResponse = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $excelResponse->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', (string) $excelResponse->headers->get('content-type'));

        // 18. Export PDF
        $pdfResponse = $this->actingAs($this->userA)->get(route('reports.export.pdf'));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('content-type'));

        // 19. Return to Dashboard
        $dashboardUpdated = $this->actingAs($this->userA)->get(route('dashboard'));
        $dashboardUpdated->assertOk();
        $dashboardUpdated->assertSee('19,200.00');
        $dashboardUpdated->assertSee('1,200.00');
        $dashboardUpdated->assertSee('120.0000');
        $dashboardUpdated->assertSee('AAPL');

        // 20. Logout User A
        $logoutResponse = $this->actingAs($this->userA)->post(route('logout'));
        $logoutResponse->assertRedirect('/');
        $this->assertGuest();

        // 21. Login as User B
        $loginBResponse = $this->post('/login', [
            'email' => 'bob.audit@example.com',
            'password' => 'Password123!',
        ]);
        $loginBResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->userB);

        // 22. Verify User A's financial data is 100% invisible to User B
        $dashboardB = $this->actingAs($this->userB)->get(route('dashboard'));
        $dashboardB->assertDontSee('AAPL');
        $dashboardB->assertDontSee('19,200.00');
        $dashboardB->assertDontSee('+1,200.00');
        $dashboardB->assertSee('No active holdings in your portfolio');
        $dashboardB->assertSee('No transactions recorded yet');

        // 23. User B cannot access User A's position detail directly
        $unauthorizedPositionResponse = $this->actingAs($this->userB)->get(route('portfolio.positions.show', $position));
        $unauthorizedPositionResponse->assertStatus(403);

        // 24. Admin also cannot access User A's personal portfolio position detail directly
        $adminPositionResponse = $this->actingAs($this->admin)->get(route('portfolio.positions.show', $position));
        $adminPositionResponse->assertStatus(403);
    }

    /**
     * Financial Calculations Precision Verification (Phase 6 Requirement 4 & 14).
     */
    public function test_financial_math_high_precision_without_float_drift(): void
    {
        // Test high precision addition and multiplication
        $a = '12345.67891234';
        $b = '98765.43219876';

        $sum = FinancialMath::add($a, $b);
        $this->assertSame('111111.11111110', $sum);

        // Test division precision
        $inv = '1000.00000000';
        $qty = '3.00000000';
        $wac = FinancialMath::divide($inv, $qty);
        $this->assertSame('333.33333333', $wac);

        // Test half-up rounding
        $this->assertSame('333.33', FinancialMath::round($wac, 2));
    }

    /**
     * Transactions Immutability Verification (Phase 6 Requirement 5).
     */
    public function test_completed_transactions_have_no_modification_routes(): void
    {
        $tx = $this->buyStock->execute($this->userA, $this->apple, '10', '100', '2026-01-01');

        // Verify that PATCH method is not supported on transaction correction route
        $patchResponse = $this->actingAs($this->userA)->patch('/stocks/'.$this->apple->id.'/transactions/'.$tx->id, [
            'quantity' => '20',
        ]);
        $this->assertSame(405, $patchResponse->status());

        // Verify that user B cannot delete or modify user A's transaction
        $unauthorizedDelete = $this->actingAs($this->userB)->delete('/stocks/'.$this->apple->id.'/transactions/'.$tx->id);
        $this->assertSame(403, $unauthorizedDelete->status());
    }

    /**
     * Server Rejects Client-Supplied Calculated Values (Phase 6 Requirement 11).
     */
    public function test_server_does_not_trust_client_supplied_financial_calculations(): void
    {
        // Attacker attempts to send spoofed amount, cost, or profit in BUY request
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->apple), [
            'company_id' => $this->apple->id,
            'quantity' => '10.0000',
            'price' => '100.0000',
            'amount' => '1.00', // Spoofed amount!
            'cost' => '1.00',   // Spoofed cost!
            'transaction_date' => '2026-01-01',
        ]);

        $response->assertRedirect(route('stocks.show', $this->apple));

        $tx = $this->apple->transactions()->where('user_id', $this->userA->id)->first();
        $this->assertNotNull($tx);
        // Server correctly computed amount = 10 * 100 = 1000, ignoring spoofed '1.00'
        $this->assertSame('1000.00000000', (string) $tx->amount);
        $this->assertSame('1000.00000000', (string) $tx->cost);
    }
}
