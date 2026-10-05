<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockWorkspaceAndLedgerTest extends TestCase
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

    /*
    |--------------------------------------------------------------------------
    | 1. Database Schema & Precision Tests
    |--------------------------------------------------------------------------
    */

    public function test_stock_positions_and_stock_transactions_tables_exist_with_columns(): void
    {
        $this->assertTrue(Schema::hasTable('stock_positions'));
        $this->assertTrue(Schema::hasColumns('stock_positions', [
            'id', 'user_id', 'company_id', 'quantity', 'average_price',
            'inventory_amount', 'realized_profit', 'last_transaction_at',
            'created_at', 'updated_at',
        ]));

        $this->assertTrue(Schema::hasTable('stock_transactions'));
        $this->assertTrue(Schema::hasColumns('stock_transactions', [
            'id', 'user_id', 'company_id', 'type', 'quantity', 'price',
            'amount', 'cost', 'realized_profit', 'transaction_date', 'notes',
            'created_at', 'updated_at',
        ]));
    }

    public function test_unique_user_company_position_constraint_is_enforced(): void
    {
        StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'quantity' => 10,
            'average_price' => 150,
            'inventory_amount' => 1500,
            'realized_profit' => 0,
        ]);

        $this->expectException(QueryException::class);

        // Attempting to insert a duplicate position for the exact same user and company
        StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'quantity' => 5,
            'average_price' => 160,
            'inventory_amount' => 800,
            'realized_profit' => 0,
        ]);
    }

    public function test_decimal_precision_is_preserved_without_floating_point_truncation(): void
    {
        $preciseQuantity = '123.45678901';
        $precisePrice = '53.33333333';
        $preciseAmount = '6584.36207997';

        $tx = StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => $preciseQuantity,
            'price' => $precisePrice,
            'amount' => $preciseAmount,
            'cost' => '0',
            'realized_profit' => '0',
            'transaction_date' => '2026-10-05',
        ]);

        $txFresh = StockTransaction::find($tx->id);

        $this->assertSame($preciseQuantity, (string) $txFresh->quantity);
        $this->assertSame($precisePrice, (string) $txFresh->price);
        $this->assertSame($preciseAmount, (string) $txFresh->amount);
    }

    public function test_foreign_key_restricts_company_deletion_when_transactions_exist(): void
    {
        StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 10,
            'price' => 100,
            'amount' => 1000,
            'transaction_date' => '2026-10-05',
        ]);

        $this->expectException(QueryException::class);

        // Deleting the company must fail due to restrictOnDelete
        $this->company->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Model & Enum Tests
    |--------------------------------------------------------------------------
    */

    public function test_transaction_type_enum_casting_and_methods(): void
    {
        $buyTx = StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 10,
            'price' => 100,
            'amount' => 1000,
            'transaction_date' => '2026-10-05',
        ]);

        $this->assertInstanceOf(TransactionType::class, $buyTx->type);
        $this->assertSame(TransactionType::BUY, $buyTx->type);
        $this->assertTrue($buyTx->type->isBuy());
        $this->assertFalse($buyTx->type->isSell());
        $this->assertSame('Buy', $buyTx->type->label());
    }

    public function test_model_relationships(): void
    {
        $position = StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'quantity' => 15,
            'average_price' => 120,
            'inventory_amount' => 1800,
            'realized_profit' => 200,
        ]);

        $tx = StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 15,
            'price' => 120,
            'amount' => 1800,
            'transaction_date' => '2026-10-05',
        ]);

        // Position relationships
        $this->assertTrue($position->user->is($this->userA));
        $this->assertTrue($position->company->is($this->company));

        // Transaction relationships
        $this->assertTrue($tx->user->is($this->userA));
        $this->assertTrue($tx->company->is($this->company));

        // User relationships
        $this->assertTrue($this->userA->stockPositions->contains($position));
        $this->assertTrue($this->userA->stockTransactions->contains($tx));

        // Company relationships
        $this->assertTrue($this->company->positions->contains($position));
        $this->assertTrue($this->company->transactions->contains($tx));
    }

    public function test_model_scopes(): void
    {
        // Positions scopes
        StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'quantity' => 50,
            'average_price' => 100,
            'inventory_amount' => 5000,
        ]);

        $company2 = Company::factory()->create(['symbol' => 'MSFT']);
        StockPosition::create([
            'user_id' => $this->userA->id,
            'company_id' => $company2->id,
            'quantity' => 0,
            'average_price' => 0,
            'inventory_amount' => 0,
        ]);

        $this->assertSame(1, StockPosition::withInventory()->count());
        $this->assertSame(1, StockPosition::empty()->count());
        $this->assertSame(2, StockPosition::forUser($this->userA)->count());
        $this->assertSame(1, StockPosition::forCompany($this->company)->count());

        // Transactions scopes
        StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 50,
            'price' => 100,
            'amount' => 5000,
            'transaction_date' => '2026-09-01',
        ]);

        StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::SELL,
            'quantity' => 10,
            'price' => 120,
            'amount' => 1200,
            'transaction_date' => '2026-09-15',
        ]);

        $this->assertSame(1, StockTransaction::buy()->count());
        $this->assertSame(1, StockTransaction::sell()->count());
        $this->assertSame(2, StockTransaction::forCompany($this->company)->count());
        $this->assertSame(2, StockTransaction::forUser($this->userA)->count());
        $this->assertSame(1, StockTransaction::betweenDates('2026-09-01', '2026-09-10')->count());
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Authorization & User Data Isolation Tests
    |--------------------------------------------------------------------------
    */

    public function test_guest_cannot_access_stock_workspace(): void
    {
        $response = $this->get(route('stocks.show', $this->company));

        $response->assertRedirect(route('login'));
    }

    public function test_user_can_access_own_workspace_with_empty_position(): void
    {
        $response = $this->actingAs($this->userA)->get(route('stocks.show', $this->company));

        $response->assertStatus(200);
        $response->assertSee('Stock Workspace');
        $response->assertSee('Apple Inc.');
        $response->assertSee('AAPL');
        $response->assertSee('Current Quantity');
        $response->assertSee('0.0000');
        $response->assertSee('No transactions recorded yet');
    }

    public function test_inactive_company_cannot_be_accessed_by_normal_user(): void
    {
        $inactiveCompany = Company::factory()->inactive()->create(['symbol' => 'INACT']);

        $response = $this->actingAs($this->userA)->get(route('stocks.show', $inactiveCompany));

        $response->assertStatus(403);
    }

    public function test_admin_can_access_inactive_company_workspace(): void
    {
        $inactiveCompany = Company::factory()->inactive()->create(['symbol' => 'INACT']);

        $response = $this->actingAs($this->admin)->get(route('stocks.show', $inactiveCompany));

        $response->assertStatus(200);
        $response->assertSee('INACT');
        $response->assertSee('Inactive');
    }

    public function test_user_a_cannot_see_user_b_transactions_or_position(): void
    {
        // User B has a position and transaction
        StockPosition::create([
            'user_id' => $this->userB->id,
            'company_id' => $this->company->id,
            'quantity' => 999.0000,
            'average_price' => 777.0000,
            'inventory_amount' => 776223.00,
            'realized_profit' => 12345.67,
        ]);

        StockTransaction::create([
            'user_id' => $this->userB->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 999.0000,
            'price' => 777.0000,
            'amount' => 776223.00,
            'transaction_date' => '2026-10-01',
            'notes' => 'User B Secret Investment Note',
        ]);

        // User A visits the exact same company workspace
        $response = $this->actingAs($this->userA)->get(route('stocks.show', $this->company));

        $response->assertStatus(200);
        // User A should NOT see User B's quantity, amounts, or notes
        $response->assertDontSee('999.0000');
        $response->assertDontSee('776223.00');
        $response->assertDontSee('12,345.67');
        $response->assertDontSee('User B Secret Investment Note');
        $response->assertSee('No transactions recorded yet');

        // User B visits and sees their own data
        $responseB = $this->actingAs($this->userB)->get(route('stocks.show', $this->company));
        $responseB->assertStatus(200);
        $responseB->assertSee('999.0000');
        $responseB->assertSee('776,223.00');
        $responseB->assertSee('12,345.67');
        $responseB->assertSee('User B Secret Investment Note');
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Immutability: No Edit/Delete Routes
    |--------------------------------------------------------------------------
    */

    public function test_no_edit_or_delete_routes_exist_for_transactions(): void
    {
        $tx = StockTransaction::create([
            'user_id' => $this->userA->id,
            'company_id' => $this->company->id,
            'type' => TransactionType::BUY,
            'quantity' => 10,
            'price' => 100,
            'amount' => 1000,
            'transaction_date' => '2026-10-05',
        ]);

        // Asserting that financial mutation routes do not exist
        $this->actingAs($this->admin)->get("/transactions/{$tx->id}/edit")->assertStatus(404);
        $this->actingAs($this->admin)->put("/transactions/{$tx->id}")->assertStatus(404);
        $this->actingAs($this->admin)->delete("/transactions/{$tx->id}")->assertStatus(404);
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Workspace Pagination Tests
    |--------------------------------------------------------------------------
    */

    public function test_workspace_transaction_pagination(): void
    {
        // Seed 25 transactions for User A
        for ($i = 1; $i <= 25; $i++) {
            StockTransaction::create([
                'user_id' => $this->userA->id,
                'company_id' => $this->company->id,
                'type' => TransactionType::BUY,
                'quantity' => 1,
                'price' => 100 + $i,
                'amount' => 100 + $i,
                'transaction_date' => '2026-10-01',
                'notes' => "Batch Note #{$i}",
            ]);
        }

        $response = $this->actingAs($this->userA)->get(route('stocks.show', $this->company));

        $response->assertStatus(200);
        $response->assertSee('Total Records: 25');
        // Page 1 should contain 20 records
        $response->assertSee('Batch Note #25'); // Latest first
    }
}
