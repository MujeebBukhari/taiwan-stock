<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditStockTransactionTest extends TestCase
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

        $this->userA = User::factory()->user()->create();
        $this->userB = User::factory()->user()->create();
        $this->admin = User::factory()->admin()->create();

        $this->company = Company::factory()->create([
            'symbol' => '2330',
            'name' => 'Taiwan Semiconductor Manufacturing Co.',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->buyStock = app(BuyStock::class);
        $this->sellStock = app(SellStock::class);
    }

    /**
     * 1. User can view transaction edit screen for their own transaction.
     */
    public function test_user_can_view_transaction_edit_screen(): void
    {
        $tx = $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');

        $response = $this->actingAs($this->userA)->get(
            route('stocks.transactions.edit', [$this->company, $tx])
        );

        $response->assertStatus(200);
        $response->assertSee('2330');
        $response->assertSee('Taiwan Semiconductor Manufacturing Co.');
        $response->assertSee('100');
        $response->assertSee('50');
    }

    /**
     * 2. User cannot view another user's transaction edit screen.
     */
    public function test_user_cannot_view_another_users_transaction_edit_screen(): void
    {
        $tx = $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');

        $response = $this->actingAs($this->userB)->get(
            route('stocks.transactions.edit', [$this->company, $tx])
        );

        $response->assertStatus(403);
    }

    /**
     * 3. Editing a BUY transaction price recalculates WAC, subsequent SELL cost basis, and realized profit.
     *
     * Timeline:
     * - Day 1 (BUY):  100 @ 10 = 1000
     * - Day 2 (BUY):  100 @ 20 = 2000  -> WAC = (1000+2000)/200 = 15.00
     * - Day 3 (SELL):  50 @ 25 = 1250  -> Cost = 50 * 15 = 750, Realized Profit = 500
     *
     * Edit Day 1 BUY price from 10 to 14:
     * - Day 1 (BUY):  100 @ 14 = 1400
     * - Day 2 (BUY):  100 @ 20 = 2000  -> Recalculated WAC = (1400+2000)/200 = 17.00
     * - Day 3 (SELL):  50 @ 25 = 1250  -> Recalculated Cost = 50 * 17 = 850, Recalculated Profit = 400
     * - Final Position: Qty = 150, WAC = 17.00, Inv = 150 * 17 = 2550, Realized Profit = 400
     */
    public function test_editing_buy_price_recalculates_wac_and_subsequent_sell_profit(): void
    {
        $buy1 = $this->buyStock->execute($this->userA, $this->company, '100', '10.00', '2026-10-01');
        $buy2 = $this->buyStock->execute($this->userA, $this->company, '100', '20.00', '2026-10-02');
        $sell = $this->sellStock->execute($this->userA, $this->company, '50', '25.00', '2026-10-03');

        // Initial check before edit
        $pos = StockPosition::query()->forUser($this->userA)->forCompany($this->company)->first();
        $this->assertSame('15.00000000', (string) $pos->average_price);
        $this->assertSame('500.00000000', (string) $pos->realized_profit);
        $this->assertSame('750.00000000', (string) $sell->fresh()->cost);

        // Edit Day 1 BUY price to 14.00
        $response = $this->actingAs($this->userA)->put(
            route('stocks.transactions.update', [$this->company, $buy1]),
            [
                'quantity' => '100',
                'price' => '14.00',
                'transaction_date' => '2026-10-01',
                'notes' => 'Adjusted buy price to 14.00',
            ]
        );

        $response->assertRedirect(route('stocks.show', $this->company));
        $response->assertSessionHas('status');

        // Verify buy1 transaction was updated
        $buy1->refresh();
        $this->assertSame('14.00000000', (string) $buy1->price);
        $this->assertSame('1400.00000000', (string) $buy1->amount);
        $this->assertSame('1400.00000000', (string) $buy1->cost);

        // Verify subsequent SELL was recalculated
        $sell->refresh();
        $this->assertSame('850.00000000', (string) $sell->cost);
        $this->assertSame('400.00000000', (string) $sell->realized_profit);

        // Verify position was recalculated
        $pos->refresh();
        $this->assertSame('150.00000000', (string) $pos->quantity);
        $this->assertSame('17.00000000', (string) $pos->average_price);
        $this->assertSame('2550.00000000', (string) $pos->inventory_amount);
        $this->assertSame('400.00000000', (string) $pos->realized_profit);
    }

    /**
     * 4. Editing a SELL transaction price recalculates selling amount and realized profit.
     */
    public function test_editing_sell_price_recalculates_realized_profit(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $sell = $this->sellStock->execute($this->userA, $this->company, '30', '60.00', '2026-10-02');

        $pos = StockPosition::query()->forUser($this->userA)->forCompany($this->company)->first();
        $this->assertSame('300.00000000', (string) $pos->realized_profit);

        // Edit sell price from 60.00 to 75.00
        $response = $this->actingAs($this->userA)->put(
            route('stocks.transactions.update', [$this->company, $sell]),
            [
                'quantity' => '30',
                'price' => '75.00',
                'transaction_date' => '2026-10-02',
                'notes' => 'Updated selling execution price',
            ]
        );

        $response->assertRedirect(route('stocks.show', $this->company));

        $sell->refresh();
        $this->assertSame('75.00000000', (string) $sell->price);
        $this->assertSame('2250.00000000', (string) $sell->amount);
        $this->assertSame('1500.00000000', (string) $sell->cost);
        $this->assertSame('750.00000000', (string) $sell->realized_profit);

        $pos->refresh();
        $this->assertSame('70.00000000', (string) $pos->quantity);
        $this->assertSame('50.00000000', (string) $pos->average_price);
        $this->assertSame('3500.00000000', (string) $pos->inventory_amount);
        $this->assertSame('750.00000000', (string) $pos->realized_profit);
    }

    /**
     * 5. Editing a SELL transaction quantity recalculates remaining shares, cost basis, and profit.
     */
    public function test_editing_sell_quantity_recalculates_remaining_shares_and_inventory(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $sell = $this->sellStock->execute($this->userA, $this->company, '30', '70.00', '2026-10-02');

        // Edit sold quantity from 30 to 50
        $response = $this->actingAs($this->userA)->put(
            route('stocks.transactions.update', [$this->company, $sell]),
            [
                'quantity' => '50',
                'price' => '70.00',
                'transaction_date' => '2026-10-02',
            ]
        );

        $response->assertRedirect(route('stocks.show', $this->company));

        $sell->refresh();
        $this->assertSame('50.00000000', (string) $sell->quantity);
        $this->assertSame('3500.00000000', (string) $sell->amount);
        $this->assertSame('2500.00000000', (string) $sell->cost);
        $this->assertSame('1000.00000000', (string) $sell->realized_profit);

        $pos = StockPosition::query()->forUser($this->userA)->forCompany($this->company)->first();
        $this->assertSame('50.00000000', (string) $pos->quantity);
        $this->assertSame('2500.00000000', (string) $pos->inventory_amount);
        $this->assertSame('1000.00000000', (string) $pos->realized_profit);
    }

    /**
     * 6. Editing transaction to overdraft available shares is rejected.
     */
    public function test_editing_transaction_to_overdraft_is_rejected(): void
    {
        $buy = $this->buyStock->execute($this->userA, $this->company, '50', '10.00', '2026-10-01');
        $sell = $this->sellStock->execute($this->userA, $this->company, '40', '15.00', '2026-10-02');

        // Attempting to edit BUY quantity to 30, which causes SELL of 40 to overdraft
        $response = $this->actingAs($this->userA)->put(
            route('stocks.transactions.update', [$this->company, $buy]),
            [
                'quantity' => '30',
                'price' => '10.00',
                'transaction_date' => '2026-10-01',
            ]
        );

        $response->assertSessionHasErrors(['quantity']);

        // Buy quantity remains unchanged
        $this->assertSame('50.00000000', (string) $buy->fresh()->quantity);
        $this->assertSame('10.00000000', (string) StockPosition::first()->quantity);
    }

    /**
     * 7. Editing SELL quantity beyond owned shares is rejected.
     */
    public function test_editing_sell_quantity_beyond_available_is_rejected(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '50', '10.00', '2026-10-01');
        $sell = $this->sellStock->execute($this->userA, $this->company, '40', '15.00', '2026-10-02');

        // Attempting to edit SELL quantity to 60 (only 50 bought)
        $response = $this->actingAs($this->userA)->put(
            route('stocks.transactions.update', [$this->company, $sell]),
            [
                'quantity' => '60',
                'price' => '15.00',
                'transaction_date' => '2026-10-02',
            ]
        );

        $response->assertSessionHasErrors(['quantity']);
        $this->assertSame('40.00000000', (string) $sell->fresh()->quantity);
    }

    /**
     * 8. User can delete a transaction and position recalculates cleanly.
     */
    public function test_user_can_delete_transaction_and_position_recalculates(): void
    {
        $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');
        $sell = $this->sellStock->execute($this->userA, $this->company, '40', '70.00', '2026-10-02');

        // Delete the sell transaction
        $response = $this->actingAs($this->userA)->delete(
            route('stocks.transactions.destroy', [$this->company, $sell])
        );

        $response->assertRedirect(route('stocks.show', $this->company));
        $this->assertDatabaseMissing('stock_transactions', ['id' => $sell->id]);

        // Position returns to 100 shares @ 50.00 with 0 realized profit
        $pos = StockPosition::query()->forUser($this->userA)->forCompany($this->company)->first();
        $this->assertSame('100.00000000', (string) $pos->quantity);
        $this->assertSame('50.00000000', (string) $pos->average_price);
        $this->assertSame('5000.00000000', (string) $pos->inventory_amount);
        $this->assertSame('0.00000000', (string) $pos->realized_profit);
    }

    /**
     * 9. User B cannot delete User A's transaction.
     */
    public function test_user_cannot_delete_another_users_transaction(): void
    {
        $tx = $this->buyStock->execute($this->userA, $this->company, '100', '50.00', '2026-10-01');

        $response = $this->actingAs($this->userB)->delete(
            route('stocks.transactions.destroy', [$this->company, $tx])
        );

        $response->assertStatus(403);
        $this->assertDatabaseHas('stock_transactions', ['id' => $tx->id]);
    }

    /**
     * 10. Verify Taiwan TWD currency can be selected when creating a company.
     */
    public function test_admin_can_select_taiwan_twd_currency_when_creating_company(): void
    {
        $payload = [
            'symbol' => '2454',
            'name' => 'MediaTek Inc.',
            'exchange' => 'TWSE',
            'sector' => 'Semiconductors',
            'currency' => 'TWD',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), $payload);

        $response->assertRedirect(route('admin.companies.index'));
        $this->assertDatabaseHas('companies', [
            'symbol' => '2454',
            'name' => 'MediaTek Inc.',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
        ]);

        $company = Company::where('symbol', '2454')->first();
        $this->assertSame('TWD', $company->currency);
        $this->assertSame('NT$', $company->currency_symbol);
        $this->assertTrue($company->isTaiwanese());
    }

    /**
     * 11. Verify company edit view renders currency dropdown with Taiwan TWD option.
     */
    public function test_company_edit_view_renders_currency_dropdown(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.companies.edit', $this->company));

        $response->assertStatus(200);
        $response->assertSee('TWD - 新臺幣 (NT$) [Taiwan]');
        $response->assertSee('USD - 美元 (US$)');
    }
}
