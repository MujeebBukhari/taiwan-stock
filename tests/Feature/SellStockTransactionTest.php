<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SellStockTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private User $admin;

    private Company $company;

    private BuyStock $buyStock;

    private SellStock $sellStock;

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
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->buyStock = app(BuyStock::class);
        $this->sellStock = app(SellStock::class);
    }

    /**
     * 1. Basic SELL creates a transaction.
     */
    public function test_basic_sell_creates_a_transaction(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertRedirect(route('stocks.show', $this->company));
        $response->assertSessionHas('status', 'SELL transaction recorded successfully.');

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertNotNull($sellTx);
        $this->assertSame($this->userA->id, $sellTx->user_id);
        $this->assertSame($this->company->id, $sellTx->company_id);
    }

    /**
     * 2. Basic SELL updates position quantity.
     */
    public function test_basic_sell_updates_position_quantity(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('60.00000000', (string) $pos->quantity);
    }

    /**
     * 3. SELL calculates selling amount correctly.
     *    40 @ 70 = 2800.00000000
     */
    public function test_sell_calculates_selling_amount_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('2800.00000000', (string) $sellTx->amount);
    }

    /**
     * 4. SELL calculates cost using current WAC.
     *    WAC = 50.00000000, 40 shares sold -> cost = 2000.00000000
     */
    public function test_sell_calculates_cost_using_current_wac(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('2000.00000000', (string) $sellTx->cost);
    }

    /**
     * 5. SELL calculates realized profit correctly.
     *    Revenue 2800 - Cost 2000 = 800.00000000
     */
    public function test_sell_calculates_realized_profit_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('800.00000000', (string) $sellTx->realized_profit);
    }

    /**
     * 6. SELL does NOT change WAC.
     */
    public function test_sell_does_not_change_wac(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $posBefore = StockPosition::first();
        $this->assertSame('50.00000000', (string) $posBefore->average_price);

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $posAfter = StockPosition::first();
        $this->assertSame('50.00000000', (string) $posAfter->average_price);
    }

    /**
     * 7. SELL decreases inventory amount correctly.
     *    Remaining: 60 shares @ WAC 50 = 3000.00000000
     */
    public function test_sell_decreases_inventory_amount_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('3000.00000000', (string) $pos->inventory_amount);
    }

    /**
     * 8. SELL increases cumulative realized profit.
     */
    public function test_sell_increases_cumulative_realized_profit(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        // First sell: 40 @ 70 -> profit 800
        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '40',
            'price' => '70.00',
            'transaction_date' => '2026-10-02',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('800.00000000', (string) $pos->realized_profit);

        // Second sell: 10 @ 60 -> profit (60 - 50) * 10 = 100 -> cumulative 900
        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '60.00',
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertSame('900.00000000', (string) $pos->fresh()->realized_profit);
    }

    /**
     * 9. SELL loss is supported.
     *    BUY 100 @ 100 (WAC=100), SELL 20 @ 80 -> profit = -400.00000000
     */
    public function test_sell_loss_is_supported(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '100', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '20',
            'price' => '80.00',
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('1600.00000000', (string) $sellTx->amount);
        $this->assertSame('2000.00000000', (string) $sellTx->cost);
        $this->assertSame('-400.00000000', (string) $sellTx->realized_profit);

        $pos = StockPosition::first();
        $this->assertSame('80.00000000', (string) $pos->quantity);
        $this->assertSame('100.00000000', (string) $pos->average_price);
        $this->assertSame('8000.00000000', (string) $pos->inventory_amount);
        $this->assertSame('-400.00000000', (string) $pos->realized_profit);
    }

    /**
     * 10. Full SELL reduces quantity to zero.
     */
    public function test_full_sell_reduces_quantity_to_zero(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('0.00000000', (string) $pos->quantity);
    }

    /**
     * 11. Full SELL reduces inventory amount to zero.
     */
    public function test_full_sell_reduces_inventory_amount_to_zero(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('0.00000000', (string) $pos->inventory_amount);
    }

    /**
     * 12. Full SELL does NOT delete the StockPosition.
     */
    public function test_full_sell_does_not_delete_stock_position(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertDatabaseHas('stock_positions', [
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
        ]);
        $this->assertSame(1, StockPosition::count());
    }

    /**
     * 13. WAC remains stored after full SELL.
     */
    public function test_wac_remains_stored_after_full_sell(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '70.00',
            'transaction_date' => '2026-10-05',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('50.00000000', (string) $pos->average_price);
        $this->assertSame('2000.00000000', (string) $pos->realized_profit);
    }

    /**
     * 14. Future BUY after full SELL works correctly (Section 20 Edge Case).
     *     BUY 100 @ 50 -> Q=100, WAC=50, Inv=5000
     *     SELL 100 @ 60 -> Q=0, WAC=50, Inv=0, Profit=1000
     *     BUY 50 @ 70 -> Q=50, WAC=70, Inv=3500, Profit MUST REMAIN 1000
     */
    public function test_future_buy_after_full_sell_preserves_realized_profit_and_resets_wac(): void
    {
        // 1. BUY 100 @ 50
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        // 2. SELL 100 @ 60
        $this->sellStock->execute($this->userA, $this->company, '100', '60', '2026-10-02');

        $pos = StockPosition::first();
        $this->assertSame('0.00000000', (string) $pos->quantity);
        $this->assertSame('0.00000000', (string) $pos->inventory_amount);
        $this->assertSame('1000.00000000', (string) $pos->realized_profit);

        // 3. BUY 50 @ 70
        $this->buyStock->execute($this->userA, $this->company, '50', '70', '2026-10-05');

        $pos->refresh();
        $this->assertSame('50.00000000', (string) $pos->quantity);
        $this->assertSame('70.00000000', (string) $pos->average_price);
        $this->assertSame('3500.00000000', (string) $pos->inventory_amount);
        $this->assertSame('1000.00000000', (string) $pos->realized_profit);
    }

    /**
     * 15. SELL when no position exists is rejected.
     */
    public function test_sell_when_no_position_exists_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 16. SELL when quantity is zero is rejected.
     */
    public function test_sell_when_quantity_is_zero_is_rejected(): void
    {
        StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'quantity' => '0.00000000',
            'average_price' => '50.00000000',
            'inventory_amount' => '0.00000000',
            'realized_profit' => '0.00000000',
        ]);

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 17. SELL greater than available quantity is rejected.
     */
    public function test_sell_greater_than_available_quantity_is_rejected(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '101',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(1, StockTransaction::count()); // Only the initial BUY
        $this->assertSame('100.00000000', (string) StockPosition::first()->quantity);
    }

    /**
     * 18. Negative quantity rejected.
     */
    public function test_negative_quantity_rejected(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '-10',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(1, StockTransaction::count());
    }

    /**
     * 19. Zero quantity rejected.
     */
    public function test_zero_quantity_rejected(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '0',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(1, StockTransaction::count());
    }

    /**
     * 20. Negative price rejected.
     */
    public function test_negative_price_rejected(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '-50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['price']);
        $this->assertSame(1, StockTransaction::count());
    }

    /**
     * 21. Zero price rejected.
     */
    public function test_zero_price_rejected(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '0',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['price']);
        $this->assertSame(1, StockTransaction::count());
    }

    /**
     * 22. Invalid company rejected.
     */
    public function test_invalid_company_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post('/stocks/999999/sell', [
            'company_id' => 999999,
            'quantity' => '10',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertStatus(404);
    }

    /**
     * 23. Inactive company rejected for normal users.
     */
    public function test_inactive_company_rejected_for_normal_users(): void
    {
        $inactiveCompany = Company::factory()->inactive()->create(['symbol' => 'INACT']);

        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $inactiveCompany), [
            'company_id' => $inactiveCompany->id,
            'quantity' => '10',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertStatus(403);
    }

    /**
     * 24. Guest cannot SELL.
     */
    public function test_guest_cannot_sell(): void
    {
        $response = $this->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * 25. User A cannot SELL User B's position.
     */
    public function test_user_a_cannot_sell_user_b_position(): void
    {
        // User B has 100 shares
        $this->buyStock->execute($this->userB, $this->company, '100', '50', '2026-10-01');

        // User A attempts to sell passing user_id = User B
        $response = $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'user_id' => $this->userB->id,
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '60.00',
            'transaction_date' => '2026-10-05',
        ]);

        // Fails because User A has no position
        $response->assertSessionHasErrors(['quantity']);

        // User B's position remains completely untouched
        $userBPos = StockPosition::where('user_id', $this->userB->id)->first();
        $this->assertSame('100.00000000', (string) $userBPos->quantity);
    }

    /**
     * 26. Client-supplied amount is ignored.
     */
    public function test_client_supplied_amount_is_ignored(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '70.00',
            'amount' => '1.00', // Spoof attempt
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('700.00000000', (string) $sellTx->amount);
    }

    /**
     * 27. Client-supplied cost is ignored.
     */
    public function test_client_supplied_cost_is_ignored(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '70.00',
            'cost' => '1.00', // Spoof attempt
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('500.00000000', (string) $sellTx->cost);
    }

    /**
     * 28. Client-supplied realized_profit is ignored.
     */
    public function test_client_supplied_realized_profit_is_ignored(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '70.00',
            'realized_profit' => '999999.00', // Spoof attempt
            'transaction_date' => '2026-10-05',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('200.00000000', (string) $sellTx->realized_profit);

        $pos = StockPosition::first();
        $this->assertSame('200.00000000', (string) $pos->realized_profit);
    }

    /**
     * 29. Failed SELL does not create a StockTransaction.
     */
    public function test_failed_sell_does_not_create_a_stock_transaction(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $initialTxCount = StockTransaction::count();

        try {
            DB::transaction(function () {
                StockTransaction::create([
                    'user_id' => $this->userA->id,
                    'company_id' => $this->company->id,
                    'type' => TransactionType::SELL,
                    'quantity' => '10',
                    'price' => '70',
                    'amount' => '700',
                    'cost' => '500',
                    'realized_profit' => '200',
                    'transaction_date' => '2026-10-05',
                ]);

                throw new Exception('Simulated fatal error during SELL execution.');
            });
        } catch (Exception $e) {
            // Expected
        }

        $this->assertSame($initialTxCount, StockTransaction::count());
    }

    /**
     * 30. Failed SELL does not modify StockPosition.
     */
    public function test_failed_sell_does_not_modify_stock_position(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $pos = StockPosition::first();

        try {
            DB::transaction(function () use ($pos) {
                $pos->update(['quantity' => '50.00000000']);
                throw new Exception('Simulated fatal error during position update.');
            });
        } catch (Exception $e) {
            // Expected
        }

        $this->assertSame('100.00000000', (string) $pos->fresh()->quantity);
    }

    /**
     * 31. Cumulative realized profit is correct after multiple SELLs (Section 9 Example).
     *     BUY 100 @ 50
     *     BUY 50 @ 60
     *     Position: Q=150, WAC=53.33333333, Inv=8000
     *     SELL 40 @ 70 -> Profit = 666.66666680, Q=110, Inv=5866.66666630
     *     SELL 10 @ 40 -> Profit = -133.33333330
     *     Cumulative Profit = 533.33333350, Q=100, Inv=5333.33333300, WAC=53.33333333
     */
    public function test_cumulative_realized_profit_is_correct_after_multiple_sells(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->company, '50', '60', '2026-10-02');

        // SELL 1: 40 @ 70
        $tx1 = $this->sellStock->execute($this->userA, $this->company, '40', '70', '2026-10-03');
        $this->assertSame('2800.00000000', (string) $tx1->amount);
        $this->assertSame('2133.33333320', (string) $tx1->cost);
        $this->assertSame('666.66666680', (string) $tx1->realized_profit);

        $pos = StockPosition::first();
        $this->assertSame('110.00000000', (string) $pos->quantity);
        $this->assertSame('53.33333333', (string) $pos->average_price);
        $this->assertSame('5866.66666630', (string) $pos->inventory_amount);
        $this->assertSame('666.66666680', (string) $pos->realized_profit);

        // SELL 2: 10 @ 40
        $tx2 = $this->sellStock->execute($this->userA, $this->company, '10', '40', '2026-10-05');
        $this->assertSame('400.00000000', (string) $tx2->amount);
        $this->assertSame('533.33333330', (string) $tx2->cost);
        $this->assertSame('-133.33333330', (string) $tx2->realized_profit);

        $pos->refresh();
        $this->assertSame('100.00000000', (string) $pos->quantity);
        $this->assertSame('53.33333333', (string) $pos->average_price);
        $this->assertSame('5333.33333300', (string) $pos->inventory_amount);
        $this->assertSame('533.33333350', (string) $pos->realized_profit);
    }

    /**
     * 32. SELL after multiple BUYs uses the current WAC (Section 2 Example).
     *     Existing: Q=150, WAC=53.33333333, Inv=8000
     *     SELL 40 @ 70
     *     Selling Amount = 2800.00000000
     *     Cost = 2133.33333320
     *     Realized Profit = 666.66666680
     *     Remaining Q = 110.00000000
     *     Remaining Inv = 5866.66666630
     *     WAC = 53.33333333
     */
    public function test_sell_after_multiple_buys_uses_the_current_wac(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $this->buyStock->execute($this->userA, $this->company, '50', '60', '2026-10-02');

        $tx = $this->sellStock->execute($this->userA, $this->company, '40', '70', '2026-10-05');

        $this->assertSame('2800.00000000', (string) $tx->amount);
        $this->assertSame('2133.33333320', (string) $tx->cost);
        $this->assertSame('666.66666680', (string) $tx->realized_profit);

        $pos = StockPosition::first();
        $this->assertSame('110.00000000', (string) $pos->quantity);
        $this->assertSame('53.33333333', (string) $pos->average_price);
        $this->assertSame('5866.66666630', (string) $pos->inventory_amount);
        $this->assertSame('666.66666680', (string) $pos->realized_profit);
    }

    /**
     * 33. SELL transaction date is stored.
     */
    public function test_sell_transaction_date_is_stored(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '60.00',
            'transaction_date' => '2026-09-20',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('2026-09-20', $sellTx->transaction_date->format('Y-m-d'));

        $pos = StockPosition::first();
        $this->assertSame('2026-09-20', $pos->last_transaction_at->format('Y-m-d'));
    }

    /**
     * 34. SELL notes are stored.
     */
    public function test_sell_notes_are_stored(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $this->actingAs($this->userA)->post(route('stocks.sell.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '60.00',
            'transaction_date' => '2026-10-05',
            'notes' => 'Sold 10 shares to rebalance allocation.',
        ]);

        $sellTx = StockTransaction::where('type', TransactionType::SELL)->first();
        $this->assertSame('Sold 10 shares to rebalance allocation.', $sellTx->notes);
    }

    /**
     * 35. Existing BUY behavior remains correct.
     */
    public function test_existing_buy_behavior_remains_correct(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $pos = StockPosition::first();
        $this->assertSame('100.00000000', (string) $pos->quantity);
        $this->assertSame('50.00000000', (string) $pos->average_price);
        $this->assertSame('5000.00000000', (string) $pos->inventory_amount);
    }

    /**
     * 36. Existing transaction immutability remains intact.
     */
    public function test_existing_transaction_immutability_remains_intact_for_sell_records(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');
        $sellTx = $this->sellStock->execute($this->userA, $this->company, '50', '70', '2026-10-05');

        $this->actingAs($this->admin)->get("/transactions/{$sellTx->id}/edit")->assertStatus(404);
        $this->actingAs($this->admin)->put("/transactions/{$sellTx->id}")->assertStatus(404);
        $this->actingAs($this->admin)->delete("/transactions/{$sellTx->id}")->assertStatus(404);
    }

    /**
     * 37. SELL form renders cleanly with company and current position.
     */
    public function test_sell_form_renders_with_company_and_current_position(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        $response = $this->actingAs($this->userA)->get(route('stocks.sell.create', $this->company));

        $response->assertStatus(200);
        $response->assertSee('Record SELL Transaction');
        $response->assertSee('Apple Inc.');
        $response->assertSee('AAPL');
        $response->assertSee('Available Shares');
        $response->assertSee('Unit Sell Price');
        $response->assertSee('Sell Quantity');
        $response->assertSee('Confirm SELL Order');
    }
}
