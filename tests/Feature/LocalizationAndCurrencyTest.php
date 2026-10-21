<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Company;
use App\Models\StockPosition;
use App\Models\StockTransaction;
use App\Models\User;
use App\Services\MarketData\MarketDataService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use App\Support\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LocalizationAndCurrencyTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private User $userB;

    private BuyStock $buyService;

    private SellStock $sellService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        $this->user = User::factory()->user()->create([
            'email' => 'investor1@example.com',
            'is_active' => true,
        ]);

        $this->userB = User::factory()->user()->create([
            'email' => 'investor2@example.com',
            'is_active' => true,
        ]);

        $this->buyService = app(BuyStock::class);
        $this->sellService = app(SellStock::class);
    }

    /**
     * 1. Default currency is TWD.
     */
    public function test_default_currency_is_twd(): void
    {
        $this->assertSame('TWD', Currency::getDefault());
        $this->assertSame('TWD', config('currency.default'));

        $company = new Company;
        $this->assertSame('TWD', $company->currency);
    }

    /**
     * 2. USD remains available as secondary currency.
     */
    public function test_usd_remains_available_as_secondary_currency(): void
    {
        $this->assertTrue(Currency::isSupported('USD'));
        $this->assertContains('USD', Currency::getSupportedCodes());
        $this->assertSame('US$', Currency::getSymbol('USD'));
    }

    /**
     * 3. VND is no longer the default currency.
     */
    public function test_vnd_is_no_longer_the_default(): void
    {
        $this->assertNotSame('VND', Currency::getDefault());
        $this->assertNotSame('VND', config('currency.default'));
    }

    /**
     * 4. TWD displays as NT$ and never as a generic $ symbol.
     */
    public function test_twd_displays_as_nt_dollar(): void
    {
        $formatted = Currency::format('1250', 'TWD');
        $this->assertStringContainsString('NT$', $formatted);
        $this->assertStringNotContainsString('US$', $formatted);
        $this->assertStringNotContainsString('₫', $formatted);

        $largeFormatted = Currency::format('85000', 'TWD');
        $this->assertStringContainsString('NT$85,000', $largeFormatted);

        // Negative values format cleanly as -NT$...
        $negativeFormatted = Currency::format('-1250', 'TWD');
        $this->assertStringContainsString('-NT$', $negativeFormatted);
        $this->assertStringNotContainsString('NT$-', $negativeFormatted);
    }

    /**
     * 5. USD displays as US$ and never as a generic $ symbol.
     */
    public function test_usd_displays_as_us_dollar(): void
    {
        $formatted = Currency::format('100.00', 'USD');
        $this->assertStringContainsString('US$', $formatted);
        $this->assertStringNotContainsString('NT$', $formatted);

        $largeFormatted = Currency::format('1250.00', 'USD');
        $this->assertStringContainsString('US$1,250.00', $largeFormatted);

        $negativeFormatted = Currency::format('-100.00', 'USD');
        $this->assertStringContainsString('-US$', $negativeFormatted);
        $this->assertStringNotContainsString('US$-', $negativeFormatted);
    }

    /**
     * 6. English locale works.
     */
    public function test_english_locale_works(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'en'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Total Inventory Cost');
        $response->assertSee('Realized Profit/Loss');
    }

    /**
     * 7. Traditional Chinese locale works.
     */
    public function test_traditional_chinese_locale_works(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('儀表板');
        $response->assertSee('庫存成本基礎');
        $response->assertSee('已實現損益');
        // Must NOT contain Simplified Chinese terms
        $response->assertDontSee('仪表板');
    }

    /**
     * 8. Locale switching works.
     */
    public function test_locale_switching_works(): void
    {
        $response = $this->actingAs($this->user)
            ->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'zh_TW']);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('locale', 'zh_TW');

        // Switch back to English
        $response2 = $this->actingAs($this->user)
            ->from(route('dashboard'))
            ->post(route('locale.update'), ['locale' => 'en']);

        $response2->assertRedirect(route('dashboard'));
        $response2->assertSessionHas('locale', 'en');

        // Disallowed locale is rejected / ignored
        $response3 = $this->actingAs($this->user)
            ->withSession(['locale' => 'en'])
            ->post(route('locale.update'), ['locale' => 'zh_CN']);

        $response3->assertRedirect();
        $this->assertSame('en', session('locale'));
    }

    /**
     * 9. Traditional Chinese is configured for Taiwan.
     */
    public function test_default_locale_and_taiwan_localization_configured(): void
    {
        $this->assertContains('zh_TW', SetLocale::SUPPORTED_LOCALES);
        $this->assertSame('zh_TW', SetLocale::DEFAULT_LOCALE);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertOk();
    }

    /**
     * 10. Current route/page remains usable after switching language.
     */
    public function test_current_route_remains_usable_after_switching_language(): void
    {
        // Switch from portfolio page
        $response = $this->actingAs($this->user)
            ->from(route('portfolio.index'))
            ->post(route('locale.update'), ['locale' => 'zh_TW']);

        $response->assertRedirect(route('portfolio.index'));

        // Follow redirect to portfolio page and verify Traditional Chinese renders
        $follow = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('portfolio.index'));

        $follow->assertOk();
        $follow->assertSee('投資組合');
    }

    /**
     * 11. Dashboard translations render.
     */
    public function test_dashboard_translations_render(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('儀表板');
        $response->assertSee('庫存成本基礎');
        $response->assertSee('已實現損益');
        $response->assertSee('進行中部位');
        $response->assertSee('持股公司數');
        $response->assertSee('快捷操作');
        $response->assertSee('新增買入交易');
        $response->assertSee('查看投資組合');
        $response->assertSee('目前持股');
        $response->assertSee('近期交易');
    }

    /**
     * 12. Navigation translations render.
     */
    public function test_navigation_translations_render(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('儀表板');
        $response->assertSee('公司');
        $response->assertSee('股票');
        $response->assertSee('投資組合');
        $response->assertSee('報表');
        $response->assertSee('分析');
        $response->assertSee('登出');
    }

    /**
     * 13. Transaction translations render.
     */
    public function test_transaction_translations_render(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('transactions.index'));

        $response->assertOk();
        $response->assertSee('交易記錄');
        $response->assertSee('股票交易');
        $response->assertSee('交易引擎');
    }

    /**
     * 14. Portfolio translations render.
     */
    public function test_portfolio_translations_render(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('portfolio.index'));

        $response->assertOk();
        $response->assertSee('投資組合');
        $response->assertSee('總庫存成本');
        $response->assertSee('已實現損益');
        $response->assertSee('總持股數量');
        $response->assertSee('尚無持股部位');
    }

    /**
     * 15. Reports translations render.
     */
    public function test_reports_translations_render(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('報表');
        $response->assertSee('歷史財務報表');
        $response->assertSee('會計模式: 成本基礎');
        $response->assertSee('目前投資組合狀況');
        $response->assertSee('匯出 Excel');
        $response->assertSee('匯出 PDF');
    }

    /**
     * 16. Analytics translations render.
     */
    public function test_analytics_translations_render(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['locale' => 'zh_TW'])
            ->get(route('analytics.index'));

        $response->assertOk();
        $response->assertSee('分析');
        $response->assertSee('投資組合視覺化分析');
        $response->assertSee('會計基礎: 加權平均成本 (WAC)');
        $response->assertSee('每月交易活動');
        $response->assertSee('每月已實現損益');
        $response->assertSee('各公司已實現損益');
        $response->assertSee('各公司庫存成本基礎');
        $response->assertSee('交易分佈');
    }

    /**
     * 17. Authentication translations render.
     */
    public function test_authentication_translations_render(): void
    {
        $response = $this->withSession(['locale' => 'zh_TW'])->get(route('login'));

        $response->assertOk();
        $response->assertSee('登入');
        $response->assertSee('電子郵件');
        $response->assertSee('密碼');
        $response->assertSee('記住我');
        $response->assertSee('忘記密碼？');
    }

    /**
     * 18. Market data continues to work without live external API dependency.
     */
    public function test_market_data_continues_to_work_with_mocked_provider(): void
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

        $company = Company::factory()->create([
            'symbol' => '2330',
            'name' => 'TSMC',
            'exchange' => 'TWSE',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $service = app(MarketDataService::class);
        $quote = $service->getQuoteForCompany($company);

        $this->assertNotNull($quote);
        $this->assertSame('2330', $quote->symbol);
        $this->assertSame('TWD', $quote->currency);

        $response = $this->actingAs($this->user)->get(route('stocks.show', $company));
        $response->assertOk();
        $response->assertSee('NT$');
        $response->assertSee('2330');
    }

    /**
     * 19. Existing BUY/SELL tests still pass & WAC + realized P/L accounting logic is intact.
     */
    public function test_buy_and_sell_accounting_logic_preserves_wac_and_realized_pl(): void
    {
        $company = Company::factory()->create([
            'symbol' => '2330',
            'name' => 'Taiwan Semiconductor',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        // BUY 1: 100 shares @ 50.00
        $this->buyService->execute($this->user, $company, '100', '50.00', '2026-10-01');

        $position = StockPosition::where('user_id', $this->user->id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        $this->assertSame('100.00000000', $position->quantity);
        $this->assertSame('50.00000000', $position->average_price);
        $this->assertSame('5000.00000000', $position->inventory_amount);

        // BUY 2: 100 shares @ 70.00 -> New WAC = (5000 + 7000) / 200 = 60.00
        $this->buyService->execute($this->user, $company, '100', '70.00', '2026-10-02');

        $position->refresh();
        $this->assertSame('200.00000000', $position->quantity);
        $this->assertSame('60.00000000', $position->average_price);
        $this->assertSame('12000.00000000', $position->inventory_amount);

        // SELL: 50 shares @ 80.00 -> Realized P/L = 50 * (80 - 60) = 1,000.00
        $sellTx = $this->sellService->execute($this->user, $company, '50', '80.00', '2026-10-03');

        $position->refresh();
        $this->assertSame('150.00000000', $position->quantity);
        $this->assertSame('60.00000000', $position->average_price); // WAC unchanged after SELL
        $this->assertSame('9000.00000000', $position->inventory_amount);
        $this->assertSame('1000.00000000', $sellTx->realized_profit);

        // Verify transaction immutability
        $this->assertCount(3, StockTransaction::where('user_id', $this->user->id)->get());
    }

    /**
     * 20. Existing portfolio and reports reflect TWD amounts and symbols.
     */
    public function test_portfolio_and_reports_reflect_twd_currency(): void
    {
        $company = Company::factory()->create([
            'symbol' => '2454',
            'name' => 'MediaTek',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->buyService->execute($this->user, $company, '10', '1000.00', '2026-10-01');

        $response = $this->actingAs($this->user)->get(route('portfolio.index'));
        $response->assertOk();
        $response->assertSee('2454');
        $response->assertSee('NT$');

        $stockResp = $this->actingAs($this->user)->get(route('stocks.show', $company));
        $stockResp->assertOk();
        $stockResp->assertSee('NT$');

        $reportResp = $this->actingAs($this->user)->get(route('reports.index'));
        $reportResp->assertOk();
        $reportResp->assertSee('2454');
        $reportResp->assertSee('MediaTek');
    }

    /**
     * 21. User isolation remains strictly preserved across locales.
     */
    public function test_user_isolation_remains_unchanged(): void
    {
        $company = Company::factory()->create([
            'symbol' => '2317',
            'name' => 'Hon Hai',
            'currency' => 'TWD',
            'is_active' => true,
        ]);

        $this->buyService->execute($this->user, $company, '50', '100.00', '2026-10-01');

        // User B cannot see User A's position in EN
        $responseEn = $this->actingAs($this->userB)->withSession(['locale' => 'en'])->get(route('portfolio.index'));
        $responseEn->assertOk();
        $responseEn->assertDontSee('2317');

        // User B cannot see User A's position in zh_TW
        $responseZh = $this->actingAs($this->userB)->withSession(['locale' => 'zh_TW'])->get(route('portfolio.index'));
        $responseZh->assertOk();
        $responseZh->assertDontSee('2317');
    }
}
