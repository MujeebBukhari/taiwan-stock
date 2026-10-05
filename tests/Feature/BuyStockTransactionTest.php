<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\FinancialMath;
use App\Services\Transactions\BuyStock;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BuyStockTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private User $admin;

    private Company $company;

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
    }

    /**
     * 1. First BUY creates a position.
     */
    public function test_first_buy_creates_a_position(): void
    {
        $this->assertDatabaseMissing('stock_positions', [
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertRedirect(route('stocks.show', $this->company));
        $response->assertSessionHas('status', 'BUY transaction recorded successfully.');

        $this->assertDatabaseHas('stock_positions', [
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
        ]);
    }

    /**
     * 2. First BUY creates exactly one transaction.
     */
    public function test_first_buy_creates_exactly_one_transaction(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '50.00',
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertSame(1, StockTransaction::where('user_id', $this->userA->id)->where('company_id', $this->company->id)->count());
    }

    /**
     * 3. First BUY calculations:
     *    100 @ 50 produces quantity = 100, amount = 5000, cost = 5000, WAC = 50, inventory = 5000, realized_profit = 0
     */
    public function test_first_buy_calculations_are_exact(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '50',
            'transaction_date' => '2026-10-05',
        ]);

        $tx = StockTransaction::first();
        $this->assertSame('100.00000000', (string) $tx->quantity);
        $this->assertSame('50.00000000', (string) $tx->price);
        $this->assertSame('5000.00000000', (string) $tx->amount);
        $this->assertSame('5000.00000000', (string) $tx->cost);
        $this->assertSame('0.00000000', (string) $tx->realized_profit);
        $this->assertSame(TransactionType::BUY, $tx->type);

        $pos = StockPosition::first();
        $this->assertSame('100.00000000', (string) $pos->quantity);
        $this->assertSame('50.00000000', (string) $pos->average_price);
        $this->assertSame('5000.00000000', (string) $pos->inventory_amount);
        $this->assertSame('0.00000000', (string) $pos->realized_profit);
    }

    /**
     * 4. Second BUY correctly calculates WAC:
     *    100 @ 50 then 50 @ 60 produces:
     *    Quantity = 150, Inventory Amount = 8000, WAC = 53.33333333
     */
    public function test_second_buy_correctly_calculates_weighted_average_cost(): void
    {
        // First BUY: 100 @ 50
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '100',
            'price' => '50',
            'transaction_date' => '2026-10-01',
        ]);

        // Second BUY: 50 @ 60
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '50',
            'price' => '60',
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertSame(2, StockTransaction::count());

        $pos = StockPosition::first();
        $this->assertSame('150.00000000', (string) $pos->quantity);
        $this->assertSame('8000.00000000', (string) $pos->inventory_amount);
        $this->assertSame('53.33333333', (string) $pos->average_price);
        $this->assertSame('0.00000000', (string) $pos->realized_profit);
    }

    /**
     * 5. Third BUY with high precision values preserves precision.
     */
    public function test_third_buy_with_high_precision_values_preserves_precision(): void
    {
        $buyStock = app(BuyStock::class);

        // 1. 100 @ 50
        $buyStock->execute($this->userA, $this->company, '100', '50', '2026-10-01');

        // 2. 50 @ 60
        $buyStock->execute($this->userA, $this->company, '50', '60', '2026-10-02');

        // 3. High precision buy: 10.12345678 shares @ 100.87654321
        // Purchase Amount = 10.12345678 * 100.87654321 = 1021.21932530 (scale 8)
        // New Quantity = 150 + 10.12345678 = 160.12345678
        // New Inventory Amount = 8000 + 1021.21932530 = 9021.21932530
        // New WAC = 9021.21932530 / 160.12345678 = 56.33914922
        $thirdTx = $buyStock->execute($this->userA, $this->company, '10.12345678', '100.87654321', '2026-10-05');

        $this->assertSame('1021.21932530', (string) $thirdTx->amount);
        $this->assertSame('1021.21932530', (string) $thirdTx->cost);

        $pos = StockPosition::first();
        $this->assertSame('160.12345678', (string) $pos->quantity);
        $this->assertSame('9021.21932530', (string) $pos->inventory_amount);

        $expectedWac = FinancialMath::divide('9021.21932530', '160.12345678', 8);
        $this->assertSame('56.33914922', $expectedWac);
        $this->assertSame('56.33914922', (string) $pos->average_price);
    }

    /**
     * 6. BUY does not change realized profit.
     */
    public function test_buy_does_not_change_realized_profit(): void
    {
        // Seed an existing position with prior accumulated realized profit (e.g. from previous trading cycles)
        $position = StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'quantity' => '100.00000000',
            'average_price' => '50.00000000',
            'inventory_amount' => '5000.00000000',
            'realized_profit' => '2500.50000000',
        ]);

        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '50',
            'price' => '60',
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertSame('2500.50000000', (string) $position->fresh()->realized_profit);
    }

    /**
     * 7. Transaction date is stored correctly.
     */
    public function test_transaction_date_is_stored_correctly(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '100',
            'transaction_date' => '2026-08-15',
        ]);

        $tx = StockTransaction::first();
        $this->assertSame('2026-08-15', $tx->transaction_date->format('Y-m-d'));

        $pos = StockPosition::first();
        $this->assertSame('2026-08-15', $pos->last_transaction_at->format('Y-m-d'));
    }

    /**
     * 8. Notes are stored correctly.
     */
    public function test_notes_are_stored_correctly(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '100',
            'transaction_date' => '2026-10-05',
            'notes' => 'Broker ticket #7781, regular purchase.',
        ]);

        $tx = StockTransaction::first();
        $this->assertSame('Broker ticket #7781, regular purchase.', $tx->notes);
    }

    /**
     * 9. Invalid quantity is rejected (non-numeric).
     */
    public function test_invalid_quantity_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => 'invalid-shares',
            'price' => '100',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 10. Zero quantity is rejected.
     */
    public function test_zero_quantity_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '0',
            'price' => '100',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 11. Negative quantity is rejected.
     */
    public function test_negative_quantity_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '-25',
            'price' => '100',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 12. Invalid price is rejected.
     */
    public function test_invalid_price_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => 'expensive',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['price']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 13. Zero price is rejected.
     */
    public function test_zero_price_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '0',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['price']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 14. Negative price is rejected.
     */
    public function test_negative_price_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '-50',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertSessionHasErrors(['price']);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 15. Invalid company is rejected.
     */
    public function test_invalid_company_is_rejected(): void
    {
        $response = $this->actingAs($this->userA)->post('/stocks/999999/buy', [
            'company_id' => 999999,
            'quantity' => '10',
            'price' => '50',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertStatus(404);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 16. Inactive company cannot be used by normal users.
     */
    public function test_inactive_company_cannot_be_bought_by_normal_user(): void
    {
        $inactiveCompany = Company::factory()->inactive()->create(['symbol' => 'INACT']);

        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $inactiveCompany), [
            'company_id' => $inactiveCompany->id,
            'quantity' => '10',
            'price' => '50',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertStatus(403);
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 17. Guest cannot create BUY transactions.
     */
    public function test_guest_cannot_create_buy_transactions(): void
    {
        $response = $this->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 18. User A cannot create a transaction for User B's position.
     */
    public function test_user_a_cannot_create_transaction_for_user_b_position(): void
    {
        // User A submits a transaction trying to pass user_id = User B
        $response = $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'user_id' => $this->userB->id, // Malicious spoof attempt
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50',
            'transaction_date' => '2026-10-05',
        ]);

        $response->assertRedirect();

        // Transaction and position must belong exclusively to User A, NOT User B
        $tx = StockTransaction::first();
        $this->assertSame($this->userA->id, $tx->user_id);

        $pos = StockPosition::first();
        $this->assertSame($this->userA->id, $pos->user_id);
        $this->assertDatabaseMissing('stock_positions', ['user_id' => $this->userB->id]);
    }

    /**
     * 19. Server calculates amount instead of trusting client-supplied amount.
     */
    public function test_server_calculates_amount_instead_of_trusting_client_supplied_amount(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50',
            'amount' => '1.00', // Spoof attempt
            'transaction_date' => '2026-10-05',
        ]);

        $tx = StockTransaction::first();
        $this->assertSame('500.00000000', (string) $tx->amount);
    }

    /**
     * 20. Server calculates cost instead of trusting client-supplied cost.
     */
    public function test_server_calculates_cost_instead_of_trusting_client_supplied_cost(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50',
            'cost' => '1.00', // Spoof attempt
            'transaction_date' => '2026-10-05',
        ]);

        $tx = StockTransaction::first();
        $this->assertSame('500.00000000', (string) $tx->cost);
    }

    /**
     * 21. Server calculates realized_profit instead of trusting client-supplied values.
     */
    public function test_server_calculates_realized_profit_instead_of_trusting_client_supplied_values(): void
    {
        $this->actingAs($this->userA)->post(route('stocks.buy.store', $this->company), [
            'company_id' => $this->company->id,
            'quantity' => '10',
            'price' => '50',
            'realized_profit' => '9999.00', // Spoof attempt
            'transaction_date' => '2026-10-05',
        ]);

        $tx = StockTransaction::first();
        $this->assertSame('0.00000000', (string) $tx->realized_profit);

        $pos = StockPosition::first();
        $this->assertSame('0.00000000', (string) $pos->realized_profit);
    }

    /**
     * 22. Failed transaction does not leave a partial StockTransaction.
     */
    public function test_failed_transaction_does_not_leave_partial_stock_transaction(): void
    {
        try {
            DB::transaction(function () {
                StockTransaction::create([
                    'user_id' => $this->userA->id,
                    'company_id' => $this->company->id,
                    'type' => TransactionType::BUY,
                    'quantity' => '10',
                    'price' => '50',
                    'amount' => '500',
                    'cost' => '500',
                    'transaction_date' => '2026-10-05',
                ]);

                throw new Exception('Simulated crash before position commit.');
            });
        } catch (Exception $e) {
            // Expected
        }

        $this->assertSame(0, StockTransaction::count());
    }

    /**
     * 23. Failed transaction does not leave a partial StockPosition.
     */
    public function test_failed_transaction_does_not_leave_partial_stock_position(): void
    {
        try {
            DB::transaction(function () {
                StockPosition::create([
                    'user_id' => $this->userA->id,
                    'company_id' => $this->company->id,
                    'quantity' => '10',
                    'average_price' => '50',
                    'inventory_amount' => '500',
                    'realized_profit' => '0',
                    'last_transaction_at' => '2026-10-05',
                ]);

                throw new Exception('Simulated crash before transaction commit.');
            });
        } catch (Exception $e) {
            // Expected
        }

        $this->assertSame(0, StockPosition::count());
    }

    /**
     * 24. Existing Phase 3A transaction immutability remains intact.
     */
    public function test_existing_transaction_immutability_remains_intact(): void
    {
        $tx = StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 10,
            'price' => 100,
            'amount' => 1000,
            'cost' => 1000,
            'transaction_date' => '2026-10-05',
        ]);

        $this->actingAs($this->admin)->get("/transactions/{$tx->id}/edit")->assertStatus(404);
        $this->actingAs($this->admin)->put("/transactions/{$tx->id}")->assertStatus(404);
        $this->actingAs($this->admin)->delete("/transactions/{$tx->id}")->assertStatus(404);
    }

    /**
     * 25. BUY form screen renders cleanly with position context.
     */
    public function test_buy_form_renders_with_company_and_current_position(): void
    {
        $response = $this->actingAs($this->userA)->get(route('stocks.buy.create', $this->company));

        $response->assertStatus(200);
        $response->assertSee('Record BUY Transaction');
        $response->assertSee('Apple Inc.');
        $response->assertSee('AAPL');
        $response->assertSee('Unit Buy Price');
        $response->assertSee('Buy Quantity');
        $response->assertSee('Confirm BUY Order');
    }
}
