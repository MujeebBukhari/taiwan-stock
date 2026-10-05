<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->user()->create();
    }

    /*
    |--------------------------------------------------------------------------
    | A. Company Creation Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_create_company_screen(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.companies.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Company Master Record');
    }

    public function test_normal_user_cannot_view_create_company_screen(): void
    {
        $response = $this->actingAs($this->user)->get(route('admin.companies.create'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_company_with_normalized_symbol(): void
    {
        $payload = [
            'symbol' => 'nvda',
            'name' => 'NVIDIA Corporation',
            'exchange' => 'nasdaq',
            'sector' => 'Technology',
            'currency' => 'usd',
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), $payload);

        $response->assertRedirect(route('admin.companies.index'));
        $response->assertSessionHas('status', 'Company created successfully.');

        $this->assertDatabaseHas('companies', [
            'symbol' => 'NVDA', // Normalized to uppercase
            'name' => 'NVIDIA Corporation',
            'exchange' => 'nasdaq',
            'currency' => 'USD', // Normalized to uppercase
            'is_active' => 1,
        ]);
    }

    public function test_normal_user_cannot_create_company(): void
    {
        $payload = [
            'symbol' => 'GOOG',
            'name' => 'Alphabet Inc.',
            'exchange' => 'NASDAQ',
            'currency' => 'USD',
        ];

        $response = $this->actingAs($this->user)->post(route('admin.companies.store'), $payload);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('companies', ['symbol' => 'GOOG']);
    }

    public function test_create_company_validation_rules(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), []);

        $response->assertSessionHasErrors(['symbol', 'name', 'exchange', 'currency']);
    }

    public function test_duplicate_symbol_is_rejected_on_creation(): void
    {
        Company::factory()->create(['symbol' => 'AAPL']);

        $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), [
            'symbol' => 'aapl', // Case-insensitive duplicate check
            'name' => 'Duplicate Apple',
            'exchange' => 'NASDAQ',
            'currency' => 'USD',
        ]);

        $response->assertSessionHasErrors(['symbol']);
    }

    /*
    |--------------------------------------------------------------------------
    | B. Company Update Tests
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_view_edit_company_screen(): void
    {
        $company = Company::factory()->create(['symbol' => 'MSFT']);

        $response = $this->actingAs($this->admin)->get(route('admin.companies.edit', $company));

        $response->assertStatus(200);
        $response->assertSee('Edit Company: MSFT');
    }

    public function test_normal_user_cannot_view_edit_company_screen(): void
    {
        $company = Company::factory()->create(['symbol' => 'MSFT']);

        $response = $this->actingAs($this->user)->get(route('admin.companies.edit', $company));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_company(): void
    {
        $company = Company::factory()->create([
            'symbol' => 'ENGRO',
            'name' => 'Engro Corp',
            'exchange' => 'PSX',
            'currency' => 'PKR',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company), [
            'symbol' => 'engro',
            'name' => 'Engro Corporation Limited',
            'exchange' => 'PSX',
            'sector' => 'Chemicals',
            'currency' => 'pkr',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.companies.index'));
        $response->assertSessionHas('status', 'Company updated successfully.');

        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'symbol' => 'ENGRO',
            'name' => 'Engro Corporation Limited',
            'sector' => 'Chemicals',
            'currency' => 'PKR',
        ]);
    }

    public function test_update_validates_duplicate_symbol_with_other_companies(): void
    {
        Company::factory()->create(['symbol' => 'AAPL']);
        $company2 = Company::factory()->create(['symbol' => 'MSFT']);

        $response = $this->actingAs($this->admin)->put(route('admin.companies.update', $company2), [
            'symbol' => 'AAPL', // Conflicts with existing AAPL
            'name' => 'Microsoft Corporation',
            'exchange' => 'NASDAQ',
            'currency' => 'USD',
        ]);

        $response->assertSessionHasErrors(['symbol']);
    }

    /*
    |--------------------------------------------------------------------------
    | C. Status Toggle Tests (Deactivate / Activate)
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_toggle_company_status(): void
    {
        $company = Company::factory()->active()->create(['symbol' => 'TSLA']);

        // Deactivate
        $response = $this->actingAs($this->admin)->patch(route('admin.companies.toggle-status', $company));
        $response->assertSessionHas('status', 'Company TSLA successfully deactivated.');
        $this->assertFalse($company->fresh()->is_active);

        // Reactivate
        $response2 = $this->actingAs($this->admin)->patch(route('admin.companies.toggle-status', $company));
        $response2->assertSessionHas('status', 'Company TSLA successfully activated.');
        $this->assertTrue($company->fresh()->is_active);
    }

    public function test_normal_user_cannot_toggle_company_status(): void
    {
        $company = Company::factory()->active()->create(['symbol' => 'TSLA']);

        $response = $this->actingAs($this->user)->patch(route('admin.companies.toggle-status', $company));

        $response->assertStatus(403);
        $this->assertTrue($company->fresh()->is_active);
    }

    /*
    |--------------------------------------------------------------------------
    | D. Search Functionality Tests
    |--------------------------------------------------------------------------
    */

    public function test_search_by_exact_symbol(): void
    {
        Company::factory()->create(['symbol' => 'AAPL', 'name' => 'Apple Inc.']);
        Company::factory()->create(['symbol' => 'MSFT', 'name' => 'Microsoft Corporation']);

        $response = $this->actingAs($this->user)->get(route('companies.index', ['search' => 'AAPL']));

        $response->assertStatus(200);
        $response->assertSee('Apple Inc.');
        $response->assertDontSee('Microsoft Corporation');
    }

    public function test_search_by_partial_symbol(): void
    {
        Company::factory()->create(['symbol' => 'TSMC', 'name' => 'Taiwan Semiconductor Manufacturing Co.']);
        Company::factory()->create(['symbol' => 'LUCK', 'name' => 'Lucky Cement Limited']);

        $response = $this->actingAs($this->user)->get(route('companies.index', ['search' => 'TSM']));

        $response->assertStatus(200);
        $response->assertSee('Taiwan Semiconductor Manufacturing Co.');
        $response->assertDontSee('Lucky Cement Limited');
    }

    public function test_search_by_partial_company_name(): void
    {
        Company::factory()->create(['symbol' => 'SYS', 'name' => 'Systems Limited']);
        Company::factory()->create(['symbol' => 'OGDC', 'name' => 'Oil & Gas Development Company']);

        $response = $this->actingAs($this->user)->get(route('companies.index', ['search' => 'Systems']));

        $response->assertStatus(200);
        $response->assertSee('Systems Limited');
        $response->assertDontSee('Oil & Gas Development Company');
    }

    public function test_search_is_case_insensitive(): void
    {
        Company::factory()->create(['symbol' => 'MEBL', 'name' => 'Meezan Bank Limited']);

        $response = $this->actingAs($this->user)->get(route('companies.index', ['search' => 'mebl']));

        $response->assertStatus(200);
        $response->assertSee('Meezan Bank Limited');
    }

    /*
    |--------------------------------------------------------------------------
    | E. Filtering Tests
    |--------------------------------------------------------------------------
    */

    public function test_filter_by_exchange(): void
    {
        Company::factory()->create(['symbol' => 'HBL', 'name' => 'Habib Bank Limited', 'exchange' => 'PSX']);
        Company::factory()->create(['symbol' => 'AMZN', 'name' => 'Amazon.com Inc.', 'exchange' => 'NASDAQ']);

        $response = $this->actingAs($this->user)->get(route('companies.index', ['exchange' => 'PSX']));

        $response->assertStatus(200);
        $response->assertSee('Habib Bank Limited');
        $response->assertDontSee('Amazon.com Inc.');
    }

    public function test_filter_by_sector(): void
    {
        Company::factory()->create(['symbol' => 'XOM', 'name' => 'Exxon Mobil', 'sector' => 'Energy']);
        Company::factory()->create(['symbol' => 'JNJ', 'name' => 'Johnson & Johnson', 'sector' => 'Healthcare']);

        $response = $this->actingAs($this->user)->get(route('companies.index', ['sector' => 'Energy']));

        $response->assertStatus(200);
        $response->assertSee('Exxon Mobil');
        $response->assertDontSee('Johnson & Johnson');
    }

    public function test_normal_user_only_sees_active_companies_even_when_requesting_inactive(): void
    {
        Company::factory()->active()->create(['symbol' => 'ACTIVE1', 'name' => 'Active Company']);
        Company::factory()->inactive()->create(['symbol' => 'INACT1', 'name' => 'Inactive Company']);

        // Normal user visits companies catalog
        $response = $this->actingAs($this->user)->get(route('companies.index', ['status' => 'inactive']));

        $response->assertStatus(200);
        $response->assertSee('Active Company');
        $response->assertDontSee('Inactive Company');
    }

    public function test_admin_can_filter_and_view_inactive_companies(): void
    {
        Company::factory()->active()->create(['symbol' => 'ACTIVE1', 'name' => 'Active Company']);
        Company::factory()->inactive()->create(['symbol' => 'INACT1', 'name' => 'Inactive Company']);

        // Admin filters by inactive status
        $response = $this->actingAs($this->admin)->get(route('companies.index', ['status' => 'inactive']));

        $response->assertStatus(200);
        $response->assertSee('Inactive Company');
        $response->assertDontSee('Active Company');
    }

    /*
    |--------------------------------------------------------------------------
    | F. General Authorization Tests
    |--------------------------------------------------------------------------
    */

    public function test_guest_cannot_access_company_pages(): void
    {
        $company = Company::factory()->create();

        $this->get(route('companies.index'))->assertRedirect(route('login'));
        $this->get(route('admin.companies.index'))->assertRedirect(route('login'));
        $this->get(route('admin.companies.create'))->assertRedirect(route('login'));
        $this->get(route('admin.companies.edit', $company))->assertRedirect(route('login'));
    }

    public function test_normal_user_cannot_access_admin_company_routes(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->user)->get(route('admin.companies.index'))->assertStatus(403);
        $this->actingAs($this->user)->get(route('admin.companies.create'))->assertStatus(403);
        $this->actingAs($this->user)->get(route('admin.companies.edit', $company))->assertStatus(403);
    }

    /*
    |--------------------------------------------------------------------------
    | G. Database Constraints & Relationships Tests
    |--------------------------------------------------------------------------
    */

    public function test_database_enforces_unique_symbol_constraint(): void
    {
        Company::factory()->create(['symbol' => 'UNIQUE1']);

        $this->expectException(QueryException::class);

        Company::factory()->create(['symbol' => 'UNIQUE1']);
    }

    public function test_company_relationships_are_prepared(): void
    {
        $company = Company::factory()->create();

        $this->assertInstanceOf(HasMany::class, $company->positions());
        $this->assertInstanceOf(HasMany::class, $company->transactions());
    }
}
