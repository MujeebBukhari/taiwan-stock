<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\Financial\ExchangeRateService;
use App\Services\MarketData\MarketDataProviderInterface;
use App\Services\MarketData\TaiwanMarketDataProvider;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class GlobalCurrencySelectorTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Company $tsmc;

    private Company $honhai;

    private BuyStock $buyService;

    private SellStock $sellService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->userA = User::factory()->user()->create([
            'name' => 'Alice Taiwan',
            'email' => 'alice@taiwanstock.test',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->userB = User::factory()->user()->create([
            'name' => 'Bob Taiwan',
            'email' => 'bob@taiwanstock.test',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->tsmc = Company::factory()->create([
            'symbol' => '2330',
            'name' => 'Taiwan Semiconductor Manufacturing Co.',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->honhai = Company::factory()->create([
            'symbol' => '2317',
            'name' => 'Hon Hai Precision Industry',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->buyService = app(BuyStock::class);
        $this->sellService = app(SellStock::class);
    }

    /**
     * 1. English and Traditional Chinese language switching.
     */
    public function test_english_and_traditional_chinese_language_switching(): void
    {
        // Switch to Traditional Chinese
        $responseZh = $this->actingAs($this->userA)
            ->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'zh_TW']);

        $responseZh->assertRedirect(route('dashboard'));
        $this->assertSame('zh_TW', session('locale'));

        $dashZh = $this->actingAs($this->userA)->withSession(['locale' => 'zh_TW'])->get(route('dashboard'));
        $dashZh->assertOk();
        $dashZh->assertSee('儀表板');
        $dashZh->assertSee('幣別:');
        $dashZh->assertDontSee('仪表板'); // No Simplified Chinese

        // Switch to English
        $responseEn = $this->actingAs($this->userA)
            ->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'en']);

        $responseEn->assertRedirect(route('dashboard'));
        $this->assertSame('en', session('locale'));

        $dashEn = $this->actingAs($this->userA)->withSession(['locale' => 'en'])->get(route('dashboard'));
        $dashEn->assertOk();
        $dashEn->assertSee('Dashboard');
        $dashEn->assertSee('Currency:');

        // Disallowed languages (Simplified Chinese and Vietnamese) are rejected
        $this->actingAs($this->userA)->post(route('locale.update'), ['locale' => 'zh_CN']);
        $this->assertSame('en', session('locale'));

        $this->actingAs($this->userA)->post(route('locale.update'), ['locale' => 'vi']);
        $this->assertSame('en', session('locale'));
    }

    /**
     * 2. TWD selection and persistence.
     */
    public function test_twd_selection_and_persistence(): void
    {
        // First set to USD to test switching back to TWD
        $this->userA->update(['currency' => 'USD']);

        $response = $this->actingAs($this->userA)
            ->from(route('dashboard'))
            ->post(route('currency.update'), ['currency' => 'TWD']);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('currency', 'TWD');

        $this->userA->refresh();
        $this->assertSame('TWD', $this->userA->currency);

        // Also test GET route /currency/TWD
        $getResp = $this->actingAs($this->userA)->get(route('currency.switch', 'TWD'));
        $getResp->assertRedirect();
        $this->assertSame('TWD', session('currency'));
    }

    /**
     * 3. USD selection and persistence.
     */
    public function test_usd_selection_and_persistence(): void
    {
        $response = $this->actingAs($this->userA)
            ->from(route('dashboard'))
            ->post(route('currency.update'), ['currency' => 'USD']);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('currency', 'USD');

        $this->userA->refresh();
        $this->assertSame('USD', $this->userA->currency);

        // GET route /currency/USD
        $getResp = $this->actingAs($this->userA)->get(route('currency.switch', 'USD'));
        $getResp->assertRedirect();
        $this->assertSame('USD', session('currency'));
    }

    /**
     * 4. Invalid currency rejection (e.g. VND, EUR, FOO).
     */
    public function test_invalid_currency_rejection(): void
    {
        // Attempt POST with VND (removed currency)
        $respVnd = $this->actingAs($this->userA)
            ->from(route('dashboard'))
            ->post(route('currency.update'), ['currency' => 'VND']);

        $respVnd->assertRedirect(route('dashboard'));
        $respVnd->assertSessionHasErrors('currency');
        $this->userA->refresh();
        $this->assertSame('TWD', $this->userA->currency);

        // Attempt POST with unsupported currency
        $respEur = $this->actingAs($this->userA)
            ->from(route('dashboard'))
            ->post(route('currency.update'), ['currency' => 'EUR']);

        $respEur->assertRedirect(route('dashboard'));
        $respEur->assertSessionHasErrors('currency');

        // Attempt GET with random code
        $respGet = $this->actingAs($this->userA)->get(route('currency.switch', 'INVALID'));
        $respGet->assertRedirect();
        $this->assertNotSame('INVALID', session('currency'));
    }

    /**
     * 5. Currency preference restoration after login.
     */
    public function test_currency_preference_restoration_after_login(): void
    {
        // User A sets currency to USD
        $this->userA->update(['currency' => 'USD']);

        // Log out user
        $this->actingAs($this->userA)->post(route('logout'));
        $this->assertGuest();

        // Login again
        $loginResp = $this->post(route('login'), [
            'email' => $this->userA->email,
            'password' => 'password',
        ]);

        $loginResp->assertRedirect();
        $this->assertAuthenticatedAs($this->userA);
        $this->assertSame('USD', session('currency'));
    }

    /**
     * 6. Correct formatting of positive and negative amounts.
     */
    public function test_correct_formatting_of_positive_and_negative_amounts(): void
    {
        // TWD formatting: NT$, 0 decimal places, sign before symbol
        $this->assertSame('NT$1,250', Currency::format('1250', 'TWD'));
        $this->assertSame('-NT$1,250', Currency::format('-1250', 'TWD'));
        $this->assertSame('+NT$1,250', Currency::format('1250', 'TWD', null, true));
        $this->assertSame('NT$0', Currency::format('0', 'TWD'));

        // USD formatting: US$, 2 decimal places, sign before symbol
        $this->assertSame('US$25.50', Currency::format('25.50', 'USD'));
        $this->assertSame('-US$25.50', Currency::format('-25.50', 'USD'));
        $this->assertSame('+US$25.50', Currency::format('25.50', 'USD', null, true));
        $this->assertSame('US$0.00', Currency::format('0', 'USD'));
    }

    /**
     * 7. Currency preference isolation between users.
     */
    public function test_currency_preference_isolation_between_users(): void
    {
        // User A switches to USD
        $this->actingAs($this->userA)->post(route('currency.update'), ['currency' => 'USD']);
        $this->userA->refresh();
        $this->assertSame('USD', $this->userA->currency);

        // User B remains TWD
        $this->userB->refresh();
        $this->assertSame('TWD', $this->userB->currency);

        // User B logging in gets TWD
        $this->actingAs($this->userB)->get(route('dashboard'));
        $this->assertSame('TWD', session('currency'));
    }

    /**
     * 8. Dashboard, portfolio, and report display behavior in selected currency.
     */
    public function test_dashboard_portfolio_and_reports_reflect_selected_currency(): void
    {
        // 100 shares of TSMC @ 1000.00 TWD = 100,000 TWD
        $this->buyService->execute($this->userA, $this->tsmc, '100', '1000.00', '2026-01-10');

        // Default TWD view
        $dashTwd = $this->actingAs($this->userA)
            ->withSession(['currency' => 'TWD'])
            ->get(route('dashboard'));

        $dashTwd->assertOk();
        $dashTwd->assertSee('NT$100,000');

        // Switch user to USD (100,000 * 0.03125 = 3,125.00 USD)
        $this->userA->update(['currency' => 'USD']);
        $dashUsd = $this->actingAs($this->userA)
            ->withSession(['currency' => 'USD', 'locale' => 'en'])
            ->get(route('dashboard'));

        $dashUsd->assertOk();
        $dashUsd->assertSee('US$3,125.00');
        $dashUsd->assertSee('Converted from TWD');

        // Check Portfolio page in USD
        $portUsd = $this->actingAs($this->userA)
            ->withSession(['currency' => 'USD'])
            ->get(route('portfolio.index'));

        $portUsd->assertOk();
        $portUsd->assertSee('US$3,125.00');

        // Check Reports page in USD
        $repUsd = $this->actingAs($this->userA)
            ->withSession(['currency' => 'USD'])
            ->get(route('reports.index'));

        $repUsd->assertOk();
        $repUsd->assertSee('US$3,125.00');
        $repUsd->assertSee('US$ USD');
    }

    /**
     * 9. Excel/PDF export currency labeling.
     */
    public function test_excel_and_pdf_export_currency_labeling(): void
    {
        $this->buyService->execute($this->userA, $this->tsmc, '100', '1000.00', '2026-01-10');

        // Export PDF with USD active
        $this->userA->update(['currency' => 'USD']);
        $pdfResp = $this->actingAs($this->userA)
            ->withSession(['currency' => 'USD'])
            ->get(route('reports.export.pdf'));

        $pdfResp->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdfResp->getContent());

        // Export Excel with USD active
        $excelResp = $this->actingAs($this->userA)
            ->withSession(['currency' => 'USD'])
            ->get(route('reports.export.excel'));

        $excelResp->assertOk();
        $spreadsheet = IOFactory::load($excelResp->getFile()->getPathname());

        $summarySheet = $spreadsheet->getSheetByName('Portfolio Summary');
        $this->assertNotNull($summarySheet);
        // Cost Basis row has converted USD value: 100,000 * 0.03125 = 3125.0
        $this->assertEquals(3125.0, (float) $summarySheet->getCell('B2')->getValue());

        // Display currency row identifies USD
        $this->assertSame('Display Currency', $summarySheet->getCell('A8')->getValue());
        $this->assertSame('USD', $summarySheet->getCell('B8')->getValue());
    }

    /**
     * 10. Exchange-rate failure handling fallback to TWD.
     */
    public function test_exchange_rate_failure_handling_falls_back_gracefully(): void
    {
        // When conversion is unavailable, formatConverted falls back to TWD format safely
        $fallback = Currency::formatConverted('1000', 'TWD', 'UNSUPPORTED_OR_MISSING_PAIR');
        $this->assertSame('NT$1,000', $fallback);

        // ExchangeRateService returns null for unsupported pairs
        $service = app(ExchangeRateService::class);
        $this->assertNull($service->getRate('TWD', 'NON_EXISTENT'));
        $this->assertNull($service->convert('100', 'TWD', 'NON_EXISTENT'));
    }

    /**
     * 11. Existing BUY/SELL calculations remain unchanged.
     */
    public function test_existing_buy_sell_calculations_remain_unchanged(): void
    {
        // BUY 100 TSMC @ 600.00
        $this->buyService->execute($this->userA, $this->tsmc, '100', '600.00', '2026-01-05');
        // BUY 100 TSMC @ 800.00 -> WAC = 700.00, Inventory = 140,000.00
        $this->buyService->execute($this->userA, $this->tsmc, '100', '800.00', '2026-01-06');

        $position = StockPosition::where('user_id', $this->userA->id)
            ->where('company_id', $this->tsmc->id)
            ->firstOrFail();

        $this->assertSame('200.00000000', $position->quantity);
        $this->assertSame('700.00000000', $position->average_price);
        $this->assertSame('140000.00000000', $position->inventory_amount);

        // SELL 50 TSMC @ 900.00 -> Realized P/L = 50 * (900 - 700) = 10,000.00
        $sellTx = $this->sellService->execute($this->userA, $this->tsmc, '50', '900.00', '2026-01-10');

        $this->assertSame('10000.00000000', $sellTx->realized_profit);
        $this->assertSame('35000.00000000', $sellTx->cost);
        $this->assertSame('45000.00000000', $sellTx->amount);

        $position->refresh();
        $this->assertSame('150.00000000', $position->quantity);
        $this->assertSame('700.00000000', $position->average_price);
        $this->assertSame('105000.00000000', $position->inventory_amount);
    }

    /**
     * 12. Historical stored amounts remain unchanged after currency switching.
     */
    public function test_historical_stored_amounts_remain_unchanged_after_currency_switching(): void
    {
        $this->buyService->execute($this->userA, $this->tsmc, '100', '500.00', '2026-01-05');
        $this->sellService->execute($this->userA, $this->tsmc, '40', '650.00', '2026-01-10');

        $buyTx = StockTransaction::where('user_id', $this->userA->id)->where('type', 'BUY')->firstOrFail();
        $sellTx = StockTransaction::where('user_id', $this->userA->id)->where('type', 'SELL')->firstOrFail();
        $position = StockPosition::where('user_id', $this->userA->id)->firstOrFail();

        $initialBuyAmount = $buyTx->amount;
        $initialSellProfit = $sellTx->realized_profit;
        $initialInventoryAmount = $position->inventory_amount;

        // Switch to USD
        $this->actingAs($this->userA)->post(route('currency.update'), ['currency' => 'USD']);

        // Assert database rows did NOT change
        $buyTx->refresh();
        $sellTx->refresh();
        $position->refresh();

        $this->assertSame($initialBuyAmount, $buyTx->amount);
        $this->assertSame($initialSellProfit, $sellTx->realized_profit);
        $this->assertSame($initialInventoryAmount, $position->inventory_amount);

        // Switch back to TWD
        $this->actingAs($this->userA)->post(route('currency.update'), ['currency' => 'TWD']);

        $buyTx->refresh();
        $sellTx->refresh();
        $position->refresh();

        $this->assertSame($initialBuyAmount, $buyTx->amount);
        $this->assertSame($initialSellProfit, $sellTx->realized_profit);
        $this->assertSame($initialInventoryAmount, $position->inventory_amount);
    }

    /**
     * 13. Taiwan market-data provider configuration and bindings.
     */
    public function test_taiwan_market_data_provider_configuration_and_bindings(): void
    {
        $provider = app(MarketDataProviderInterface::class);
        $this->assertInstanceOf(TaiwanMarketDataProvider::class, $provider);

        $defaultProvider = config('market_data.default');
        $this->assertSame('taiwan', $defaultProvider);

        $taiwanConfig = config('market_data.providers.taiwan');
        $this->assertNotNull($taiwanConfig);
        $this->assertArrayNotHasKey('vietnam', config('market_data.providers'));
    }

    /**
     * 14. No obsolete Vietnam branding remains in user-facing pages.
     */
    public function test_no_obsolete_vietnam_branding_in_user_facing_pages(): void
    {
        $routes = [
            route('dashboard'),
            route('portfolio.index'),
            route('reports.index'),
            route('analytics.index'),
            route('stocks.index'),
            route('transactions.index'),
            route('companies.index'),
        ];

        foreach ($routes as $url) {
            $response = $this->actingAs($this->userA)->get($url);
            $response->assertOk();

            $content = $response->getContent();
            $this->assertStringNotContainsStringIgnoringCase('Vietnam', $content);
            $this->assertStringNotContainsStringIgnoringCase('Vietnamese', $content);
            $this->assertStringNotContainsStringIgnoringCase('Viet Nam', $content);
            $this->assertStringNotContainsStringIgnoringCase('VND', $content);
        }
    }

    /**
     * 15. Exchange-rate metadata, source transparency, and estimate flag.
     */
    public function test_exchange_rate_source_and_estimate_metadata(): void
    {
        $details = Currency::getRateDetails('TWD', 'USD');

        $this->assertSame('0.03125000', $details['rate']);
        $this->assertSame('TWD', $details['from']);
        $this->assertSame('USD', $details['to']);
        $this->assertTrue($details['is_estimate']);
        $this->assertIsInt($details['timestamp']);
        $this->assertNotEmpty($details['source']);

        // Identity conversion is not an estimate
        $identity = Currency::getRateDetails('TWD', 'TWD');
        $this->assertSame('1.00000000', $identity['rate']);
        $this->assertFalse($identity['is_estimate']);
        $this->assertSame('identity', $identity['source']);
    }
}
