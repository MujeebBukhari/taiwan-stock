<?php

namespace Tests\Feature\MarketData;

use App\Models\Company;
use App\Models\User;
use App\Services\MarketData\MarketDataService;
use App\Services\MarketData\TaiwanMarketDataProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TaiwanMarketDataTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::factory()->user()->create();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_taiwan_provider_fetches_and_normalizes_twse_quote(): void
    {
        Http::fake([
            'https://mis.twse.com.tw/*' => Http::response([
                'msgArray' => [
                    [
                        'c' => '2330',
                        'z' => '980.00',
                        'y' => '970.00',
                        'change' => '10.00',
                        'change_percent' => '1.03',
                        'v' => '25000000',
                        'n' => 'TSMC',
                        'ex' => 'tse',
                    ],
                ],
            ], 200),
        ]);

        $provider = new TaiwanMarketDataProvider;
        $quote = $provider->getQuote('2330', 'TWSE');

        $this->assertNotNull($quote);
        $this->assertSame('2330', $quote->symbol);
        $this->assertSame('TWSE', $quote->exchange);
        $this->assertSame('980', $quote->price);
        $this->assertSame('970', $quote->previousClose);
        $this->assertSame('10', $quote->change);
        $this->assertSame('1.03', $quote->changePercent);
        $this->assertSame('25000000', $quote->volume);
        $this->assertSame('TWD', $quote->currency);
    }

    public function test_taiwan_provider_supports_twse_and_tpex_exchanges(): void
    {
        $provider = new TaiwanMarketDataProvider;

        $this->assertTrue($provider->supportsExchange('TWSE'));
        $this->assertTrue($provider->supportsExchange('TPEx'));
        $this->assertTrue($provider->supportsExchange('TPEX'));
        $this->assertTrue($provider->supportsExchange('twse'));
        $this->assertTrue($provider->supportsExchange('tpex'));
    }

    public function test_taiwan_provider_handles_http_errors_gracefully(): void
    {
        Http::fake([
            'https://mis.twse.com.tw/*' => Http::response(null, 500),
        ]);

        $provider = new TaiwanMarketDataProvider;
        $quote = $provider->getQuote('2330', 'TWSE');

        $this->assertNull($quote);
    }

    public function test_market_data_service_fetches_and_caches_quote_for_company(): void
    {
        Http::fake([
            'https://mis.twse.com.tw/*' => Http::response([
                'msgArray' => [
                    [
                        'c' => '2317',
                        'z' => '185.00',
                        'y' => '180.00',
                        'change' => '5.00',
                        'change_percent' => '2.78',
                        'v' => '45000000',
                        'n' => 'Hon Hai',
                        'ex' => 'tse',
                    ],
                ],
            ], 200),
        ]);

        $company = Company::factory()->create([
            'symbol' => '2317',
            'name' => 'Hon Hai Precision Industry',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $service = app(MarketDataService::class);
        $quote = $service->getQuoteForCompany($company);

        $this->assertNotNull($quote);
        $this->assertSame('2317', $quote->symbol);
        $this->assertSame('185', $quote->price);
        $this->assertSame('TWD', $quote->currency);

        $cachedQuote = Cache::get('market_quote:2317');
        $this->assertNotNull($cachedQuote);
        $this->assertSame('185', $cachedQuote['price']);
    }

    public function test_admin_can_create_company_with_taiwan_exchanges_and_twd(): void
    {
        foreach (['TWSE', 'TPEx'] as $exchange) {
            $response = $this->actingAs($this->admin)->post(route('admin.companies.store'), [
                'symbol' => 'TW_'.$exchange,
                'name' => "Taiwan Test {$exchange}",
                'exchange' => $exchange,
                'currency' => 'TWD',
                'sector' => 'Technology',
                'is_active' => true,
            ]);

            $response->assertRedirect(route('admin.companies.index'));

            $this->assertDatabaseHas('companies', [
                'symbol' => 'TW_'.strtoupper($exchange),
                'exchange' => $exchange,
                'currency' => 'TWD',
            ]);
        }
    }

    public function test_stock_show_page_renders_market_quote_banner(): void
    {
        Http::fake([
            'https://mis.twse.com.tw/*' => Http::response([
                'msgArray' => [
                    [
                        'c' => '2454',
                        'z' => '1250.00',
                        'y' => '1230.00',
                        'change' => '20.00',
                        'change_percent' => '1.63',
                        'v' => '8000000',
                        'n' => 'MediaTek',
                        'ex' => 'tse',
                    ],
                ],
            ], 200),
        ]);

        $company = Company::factory()->create([
            'symbol' => '2454',
            'name' => 'MediaTek Inc.',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->get(route('stocks.show', $company));

        $response->assertOk();
        $response->assertSee('2454');
        $response->assertSee('MediaTek Inc.');
        $response->assertSee('NT$');
        $response->assertSee('TWSE');
    }

    public function test_no_external_api_calls_made_when_cached_quote_exists(): void
    {
        Http::fake([
            'https://mis.twse.com.tw/*' => Http::response([
                'msgArray' => [
                    [
                        'c' => '2330',
                        'z' => '980.00',
                        'y' => '970.00',
                        'change' => '10.00',
                        'change_percent' => '1.03',
                        'v' => '25000000',
                        'n' => 'TSMC',
                        'ex' => 'tse',
                    ],
                ],
            ], 200),
        ]);

        $service = app(MarketDataService::class);

        // First call - triggers 1 HTTP request
        $quote1 = $service->getQuote('2330', 'TWSE');
        $this->assertNotNull($quote1);
        Http::assertSentCount(1);

        // Second call - served from cache, zero additional HTTP requests
        $quote2 = $service->getQuote('2330', 'TWSE');
        $this->assertNotNull($quote2);
        Http::assertSentCount(1);
    }
}
