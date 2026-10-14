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
            ->with('FPT', 'HOSE')
            ->andReturn(new MarketQuote(
                symbol: 'FPT',
                exchange: 'HOSE',
                price: '128500.00',
                name: 'FPT Corporation',
                previousClose: '126000.00',
                change: '2500.00',
                changePercent: '1.98',
                volume: '4500000',
                timestamp: Carbon::now(),
                currency: 'VND'
            ));

        $service = new MarketDataService($mockProvider);

        $company = new Company([
            'symbol' => 'FPT',
            'name' => 'FPT Corporation',
            'exchange' => 'HOSE',
            'currency' => 'VND',
        ]);

        $quote = $service->getQuote($company);

        $this->assertNotNull($quote);
        $this->assertSame('FPT', $quote->symbol);
        $this->assertSame('HOSE', $quote->exchange);
        $this->assertSame('128500.00', $quote->price);
        $this->assertSame('2500.00', $quote->change);
        $this->assertSame('1.98', $quote->changePercent);
        $this->assertSame('4500000', $quote->volume);
        $this->assertTrue($quote->isPositiveChange());
        $this->assertSame('+₫2,500', $quote->formattedChange());
        $this->assertSame('+1.98%', $quote->formattedChangePercent());
    }

    public function test_market_data_service_uses_cache_and_avoids_provider_call(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        // Provider should only be called once
        $mockProvider->shouldReceive('getQuote')
            ->once()
            ->with('VNM', null)
            ->andReturn(new MarketQuote(
                symbol: 'VNM',
                exchange: 'HOSE',
                price: '68200.00',
                currency: 'VND'
            ));

        $service = new MarketDataService($mockProvider);

        // First call populates cache
        $quote1 = $service->getQuote('VNM');
        $this->assertNotNull($quote1);
        $this->assertSame('68200.00', $quote1->price);

        // Second call retrieves from cache without hitting provider
        $quote2 = $service->getQuote('VNM');
        $this->assertNotNull($quote2);
        $this->assertSame('68200.00', $quote2->price);
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
            ->with('HPG', null)
            ->andReturn(null); // Provider fails

        // Seed fallback cache
        Cache::put('market_quote_fallback:HPG', [
            'symbol' => 'HPG',
            'exchange' => 'HOSE',
            'price' => '28400.00',
            'name' => 'Hoa Phat Group',
            'currency' => 'VND',
        ], 86400);

        $service = new MarketDataService($mockProvider);

        $quote = $service->getQuote('HPG');

        $this->assertNotNull($quote);
        $this->assertSame('HPG', $quote->symbol);
        $this->assertSame('28400.00', $quote->price);
    }

    public function test_market_value_and_unrealized_profit_calculations(): void
    {
        $mockProvider = Mockery::mock(MarketDataProviderInterface::class);
        $service = new MarketDataService($mockProvider);

        // 100 shares @ 125,000 VND = 12,500,000.00 VND
        $marketValue = $service->calculateMarketValue('100.0000', '125000.0000');
        $this->assertSame('12500000.00', $marketValue);

        // Market Value 12,500,000 - Inventory Cost 10,000,000 = +2,500,000.00
        $unrealizedProfit = $service->calculateUnrealizedProfit('100.0000', '125000.0000', '10000000.00');
        $this->assertSame('2500000.00', $unrealizedProfit);

        // Percentage: (2,500,000 / 10,000,000) * 100 = 25.00%
        $percent = $service->calculateUnrealizedProfitPercent($unrealizedProfit, '10000000.00');
        $this->assertSame('25.00', $percent);
    }
}
