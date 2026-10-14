<?php

namespace Tests\Feature\MarketData;

use App\Models\Company;
use App\Models\User;
use App\Services\MarketData\MarketDataService;
use App\Services\MarketData\VietnamMarketDataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VietnamMarketDataTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config(['market_data.default' => 'vietnam']);

        $this->user = User::factory()->user()->create();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_vietnam_provider_fetches_and_normalizes_hose_quote(): void
    {
        Http::fake([
            'https://apipubaws.tcbs.com.vn/*' => Http::response([
                [
                    'ticker' => 'FPT',
                    'price' => 128500,
                    'cp' => 126000,
                    'ch' => 2500,
                    'ra' => 1.98,
                    'vo' => 3800000,
                    'companyName' => 'FPT Corporation',
                    'exchange' => 'HOSE',
                ],
            ], 200),
        ]);

        $provider = new VietnamMarketDataProvider;
        $quote = $provider->getQuote('FPT', 'HOSE');

        $this->assertNotNull($quote);
        $this->assertSame('FPT', $quote->symbol);
        $this->assertSame('HOSE', $quote->exchange);
        $this->assertSame('128500', $quote->price);
        $this->assertSame('126000', $quote->previousClose);
        $this->assertSame('2500', $quote->change);
        $this->assertSame('1.98', $quote->changePercent);
        $this->assertSame('3800000', $quote->volume);
        $this->assertSame('VND', $quote->currency);
    }

    public function test_vietnam_provider_supports_hnx_and_upcom_exchanges(): void
    {
        $provider = new VietnamMarketDataProvider;

        $this->assertTrue($provider->supportsExchange('HOSE'));
        $this->assertTrue($provider->supportsExchange('HNX'));
        $this->assertTrue($provider->supportsExchange('UPCOM'));
        $this->assertTrue($provider->supportsExchange('hose'));
        $this->assertTrue($provider->supportsExchange('hnx'));
        $this->assertTrue($provider->supportsExchange('upcom'));
    }

    public function test_vietnam_provider_handles_http_errors_gracefully(): void
    {
        Http::fake([
            'https://apipubaws.tcbs.com.vn/*' => Http::response(null, 500),
        ]);

        $provider = new VietnamMarketDataProvider;
        $quote = $provider->getQuote('VNM', 'HOSE');

        $this->assertNull($quote);
    }

    public function test_stock_workspace_displays_market_price_and_currency_symbol(): void
    {
        Http::fake([
            'https://apipubaws.tcbs.com.vn/*' => Http::response([
                [
                    'ticker' => 'FPT',
                    'price' => 135000,
                    'cp' => 132000,
                    'ch' => 3000,
                    'ra' => 2.27,
                    'vo' => 5000000,
                    'companyName' => 'FPT Corporation',
                    'exchange' => 'HOSE',
                ],
            ], 200),
        ]);

        $company = Company::factory()->create([
            'symbol' => 'FPT',
            'name' => 'FPT Corporation',
            'exchange' => 'HOSE',
            'currency' => 'VND',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('stocks.show', $company));

        $response->assertOk();
        $response->assertSee('Market Price');
        $response->assertSee('135,000');
        $response->assertSee('₫');
        $response->assertSee('+2.27%');
    }

    public function test_stock_workspace_displays_unavailable_message_when_provider_fails(): void
    {
        Http::fake([
            'https://apipubaws.tcbs.com.vn/*' => Http::response(null, 503),
        ]);

        $company = Company::factory()->create([
            'symbol' => 'SSI',
            'name' => 'SSI Securities',
            'exchange' => 'HOSE',
            'currency' => 'VND',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('stocks.show', $company));

        $response->assertOk();
        $response->assertSee('Market price unavailable');
        $response->assertDontSee('₫0');
    }

    public function test_admin_can_create_company_with_vietnam_exchanges_and_vnd(): void
    {
        foreach (['HOSE', 'HNX', 'UPCOM'] as $exchange) {
            $payload = [
                'symbol' => 'VN_'.$exchange,
                'name' => "Vietnam Test {$exchange}",
                'exchange' => $exchange,
                'currency' => 'VND',
                'sector' => 'Financials',
                'is_active' => true,
            ];

            $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), $payload);
            $response->assertRedirect(route('admin.companies.index'));

            $this->assertDatabaseHas('companies', [
                'symbol' => 'VN_'.$exchange,
                'exchange' => $exchange,
                'currency' => 'VND',
            ]);
        }
    }

    public function test_no_external_api_calls_made_when_cached_quote_exists(): void
    {
        Http::fake([
            'https://apipubaws.tcbs.com.vn/*' => Http::response([
                [
                    'ticker' => 'VCB',
                    'price' => 92000,
                    'exchange' => 'HOSE',
                ],
            ], 200),
        ]);

        $service = app(MarketDataService::class);

        // First call - triggers 1 HTTP request
        $quote1 = $service->getQuote('VCB');
        $this->assertNotNull($quote1);
        Http::assertSentCount(1);

        // Second call - served from cache, zero additional HTTP requests
        $quote2 = $service->getQuote('VCB');
        $this->assertNotNull($quote2);
        Http::assertSentCount(1);
    }
}
