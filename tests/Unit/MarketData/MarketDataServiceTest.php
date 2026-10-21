<?php

namespace Tests\Unit\MarketData;

use App\Models\Company;
use App\Services\MarketData\MarketDataProviderInterface;
use App\Services\MarketData\MarketDataService;
use App\Services\MarketData\MarketQuote;
use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class MarketDataServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Cache::flush();
        Mockery::close();
        parent::tearDown();
    }

    public function test_currency_defaults_to_twd_and_usd_remains_secondary(): void
    {
        $this->assertSame('TWD', Currency::default());
        $this->assertNotSame('VND', Currency::default());
        $this->assertSame('USD', Currency::secondary());
        $this->assertSame('NT$', Currency::symbol('TWD'));
        $this->assertSame('US$', Currency::symbol('USD'));
        $this->assertSame('New Taiwan Dollar', Currency::name('TWD'));
        $this->assertSame('US Dollar', Currency::name('USD'));
        $this->assertTrue(Currency::isSupported('TWD'));
        $this->assertTrue(Currency::isSupported('USD'));
        $this->assertSame('NT$1,250', Currency::format('1250', 'TWD'));
        $this->assertSame('US$1,250.00', Currency::format('1250', 'USD'));
    }

    public function test_market_data_service_returns_normalized_quote(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        $mockProvider->shouldReceive('getQuote')
            ->once()
            ->with('2330', 'TWSE')
            ->andReturn(new MarketQuote(
                symbol: '2330',
                exchange: 'TWSE',
                price: '980.00',
                name: 'TSMC',
                previousClose: '970.00',
                change: '10.00',
                changePercent: '1.03',
                volume: '25000000',
                timestamp: Carbon::now(),
                currency: 'TWD'
            ));

        $service = new MarketDataService($mockProvider);

        $company = new Company([
            'symbol' => '2330',
            'name' => 'TSMC',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
        ]);

        $quote = $service->getQuote($company);

        $this->assertNotNull($quote);
        $this->assertSame('2330', $quote->symbol);
        $this->assertSame('TWSE', $quote->exchange);
        $this->assertSame('980.00', $quote->price);
        $this->assertSame('10.00', $quote->change);
        $this->assertSame('1.03', $quote->changePercent);
        $this->assertSame('25000000', $quote->volume);
        $this->assertTrue($quote->isPositiveChange());
        $this->assertSame('+NT$10', $quote->formattedChange());
        $this->assertSame('+1.03%', $quote->formattedChangePercent());
    }

    public function test_market_data_service_uses_cache_and_avoids_provider_call(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        // Provider should only be called once
        $mockProvider->shouldReceive('getQuote')
            ->once()
            ->with('2317', null)
            ->andReturn(new MarketQuote(
                symbol: '2317',
                exchange: 'TWSE',
                price: '185.00',
                currency: 'TWD'
            ));

        $service = new MarketDataService($mockProvider);

        // First call populates cache
        $quote1 = $service->getQuote('2317');
        $this->assertNotNull($quote1);
        $this->assertSame('185.00', $quote1->price);

        // Second call retrieves from cache without hitting provider
        $quote2 = $service->getQuote('2317');
        $this->assertNotNull($quote2);
        $this->assertSame('185.00', $quote2->price);
    }

    public function test_provider_failure_is_handled_gracefully_without_exception(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        $mockProvider->shouldReceive('getQuote')
            ->once()
            ->with('FAIL', null)
            ->andReturn(null);

        $service = new MarketDataService($mockProvider);

        $quote = $service->getQuote('FAIL');

        $this->assertNull($quote);
    }

    public function test_provider_failure_uses_fallback_cache_when_available(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        $mockProvider->shouldReceive('getQuote')
            ->once()
            ->with('2454', null)
            ->andReturn(null); // Provider fails

        // Seed fallback cache
        Cache::put('market_quote_fallback:2454', [
            'symbol' => '2454',
            'exchange' => 'TWSE',
            'price' => '1250.00',
            'name' => 'MediaTek Inc.',
            'currency' => 'TWD',
        ], 86400);

        $service = new MarketDataService($mockProvider);

        $quote = $service->getQuote('2454');

        $this->assertNotNull($quote);
        $this->assertSame('2454', $quote->symbol);
        $this->assertSame('1250.00', $quote->price);
    }

    public function test_market_value_and_unrealized_profit_calculations(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        $service = new MarketDataService($mockProvider);

        // 100 shares @ 980 TWD = 98,000.00 TWD
        $marketValue = $service->calculateMarketValue('100.0000', '980.0000');
        $this->assertSame('98000.00', $marketValue);

        // Market Value 98,000 - Inventory Cost 80,000 = +18,000.00
        $unrealizedProfit = $service->calculateUnrealizedProfit('100.0000', '980.0000', '80000.00');
        $this->assertSame('18000.00', $unrealizedProfit);

        // Percentage: (18,000 / 80,000) * 100 = 22.50%
        $percent = $service->calculateUnrealizedProfitPercent($unrealizedProfit, '80000.00');
        $this->assertSame('22.50', $percent);
    }
}
