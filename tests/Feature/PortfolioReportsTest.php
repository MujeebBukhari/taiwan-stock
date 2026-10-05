<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\StockPosition;
use App\Models\User;
use App\Services\Portfolio\PortfolioReportService;
use App\Services\Portfolio\PortfolioService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Company $apple;

    private Company $microsoft;

    private Company $pfizer;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    private PortfolioReportService $reportService;

    private PortfolioService $portfolioService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create(['name' => 'Investor Alice']);
        $this->userB = User::factory()->user()->create(['name' => 'Investor Bob']);

        $this->apple = Company::factory()->create([
            'symbol' => 'AAPL',
            'name' => 'Apple Inc.',
            'exchange' => 'NASDAQ',
            'sector' => 'Technology',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->microsoft = Company::factory()->create([
            'symbol' => 'MSFT',
            'name' => 'Microsoft Corporation',
            'exchange' => 'NASDAQ',
            'sector' => 'Technology',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->pfizer = Company::factory()->create([
            'symbol' => 'PFE',
            'name' => 'Pfizer Inc.',
            'exchange' => 'NYSE',
            'sector' => 'Healthcare',
            'currency' => 'USD',
            'is_active' => true,
        ]);

        $this->buyStock = app(BuyStock::class);
        $this->sellStock = app(SellStock::class);
        $this->reportService = app(PortfolioReportService::class);
        $this->portfolioService = app(PortfolioService::class);
    }

    /**
     * 1. Guest cannot access reports.
     */
    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Authenticated user can access reports.
     */
    public function test_authenticated_user_can_access_reports(): void
    {
        $response = $this->actingAs($this->userA)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Historical Financial Reports');
        $response->assertSee('Current Portfolio Status');
        $response->assertSee('Historical Transaction Activity');
        $response->assertSee('Company-Wise Portfolio Summary');
        $response->assertSee('Monthly Historical Activity');
        $response->assertSee('Annual Summary');
        $response->assertSee('Realized Profit / Loss Analysis by Company');
    }

    /**
     * 3. Empty report displays correctly.
     */
    public function test_empty_report_displays_correctly(): void
    {
        $response = $this->actingAs($this->userA)->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('0.00');
        $response->assertSee('No company records found');
        $response->assertSee('No monthly transactions found');
        $response->assertSee('No annual transaction data available');
        $response->assertSee('No realized profit records found');

        $activity = $this->reportService->getTransactionActivity($this->userA);
        $this->assertSame(0, $activity['total_buy_transactions']);
        $this->assertSame(0, $activity['total_sell_transactions']);
        $this->assertSame('0.00000000', $activity['total_buy_quantity']);
        $this->assertSame('0.00000000', $activity['total_sell_quantity']);
        $this->assertSame('0.00000000', $activity['total_buy_amount']);
        $this->assertSame('0.00000000', $activity['total_sell_amount']);
        $this->assertSame('0.00000000', $activity['total_sell_cost']);
        $this->assertSame('0.00000000', $activity['total_realized_profit']);

        $companyReport = $this->reportService->getCompanyWiseReport($this->userA);
        $this->assertEmpty($companyReport);

        $monthlyReport = $this->reportService->getMonthlyReport($this->userA);
        $this->assertEmpty($monthlyReport);

        $annualReport = $this->reportService->getAnnualReport($this->userA);
        $this->assertEmpty($annualReport);

        $realizedProfit = $this->reportService->getRealizedProfitAnalysis($this->userA);
        $this->assertEmpty($realizedProfit);
    }

    /**
     * 4. Portfolio summary is correct.
     */
    public function test_portfolio_summary_is_correct(): void
    {
        // Buy 100 Apple @ 150.25 on 2026-01-10 ($15,025.00)
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.25000000', '2026-01-10');
        // Buy 50 Microsoft @ 200.50 on 2026-01-12 ($10,025.00)
        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '200.50000000', '2026-01-12');
        // Sell 20 Apple @ 180.00 on 2026-01-20 (Cost: 20 * 150.25 = 3,005.00; Profit: 20 * 29.75 = 595.00)
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '180.00000000', '2026-01-20');

        $summary = $this->reportService->getPortfolioSummary($this->userA);

        // Apple remaining: 80 @ 150.25 = 12,020.00
        // Microsoft remaining: 50 @ 200.50 = 10,025.00
        // Total cost basis: 22,045.00
        $this->assertSame('22045.00000000', $summary['total_inventory_cost']);
        $this->assertSame('130.00000000', $summary['total_quantity']);
        $this->assertSame(2, $summary['active_positions_count']);
        $this->assertSame(2, $summary['total_positions_count']);
        $this->assertSame(2, $summary['companies_held_count']);
        $this->assertSame('595.00000000', $summary['total_realized_profit']);
    }

    /**
     * 5. BUY transaction totals are correct.
     */
    public function test_buy_transaction_totals_are_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-05');
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '110.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '25.00000000', '200.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        $this->assertSame(3, $activity['total_buy_transactions']);
        $this->assertSame(0, $activity['total_sell_transactions']);
        $this->assertSame(3, $activity['total_transactions']);
    }

    /**
     * 6. SELL transaction totals are correct.
     */
    public function test_sell_transaction_totals_are_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '125.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame(2, $activity['total_sell_transactions']);
        $this->assertSame(3, $activity['total_transactions']);
    }

    /**
     * 7. BUY quantity aggregation is correct.
     */
    public function test_buy_quantity_aggregation_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '12.34567891', '100.00000000', '2026-01-05');
        $this->buyStock->execute($this->userA, $this->apple, '23.45678912', '110.00000000', '2026-01-10');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        // 12.34567891 + 23.45678912 = 35.80246803
        $this->assertSame('35.80246803', $activity['total_buy_quantity']);
    }

    /**
     * 8. SELL quantity aggregation is correct.
     */
    public function test_sell_quantity_aggregation_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '11.11111111', '120.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '22.22222222', '130.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        // 11.11111111 + 22.22222222 = 33.33333333
        $this->assertSame('33.33333333', $activity['total_sell_quantity']);
    }

    /**
     * 9. BUY amount aggregation is correct.
     */
    public function test_buy_amount_aggregation_is_correct(): void
    {
        // 10 shares @ 100.50 = 1005.00
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.50000000', '2026-01-05');
        // 20 shares @ 150.25 = 3005.00
        $this->buyStock->execute($this->userA, $this->microsoft, '20.00000000', '150.25000000', '2026-01-10');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        // 1005.00 + 3005.00 = 4010.00000000
        $this->assertSame('4010.00000000', $activity['total_buy_amount']);
    }

    /**
     * 10. SELL amount aggregation is correct.
     */
    public function test_sell_amount_aggregation_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        // 15 shares @ 120.00 = 1800.00
        $this->sellStock->execute($this->userA, $this->apple, '15.00000000', '120.00000000', '2026-01-10');
        // 25 shares @ 140.00 = 3500.00
        $this->sellStock->execute($this->userA, $this->apple, '25.00000000', '140.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        // 1800.00 + 3500.00 = 5300.00000000
        $this->assertSame('5300.00000000', $activity['total_sell_amount']);
    }

    /**
     * 11. SELL cost aggregation is correct.
     */
    public function test_sell_cost_aggregation_is_correct(): void
    {
        // Buy 100 @ 100.00 = 10,000.00 (WAC: 100.00)
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        // Buy 100 @ 200.00 = 20,000.00 => New WAC: 30,000 / 200 = 150.00
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '200.00000000', '2026-01-10');

        // Sell 50 @ 180.00 => Cost = 50 * 150.00 = 7500.00
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '180.00000000', '2026-01-15');
        // Sell 30 @ 210.00 => Cost = 30 * 150.00 = 4500.00
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '210.00000000', '2026-01-20');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        // Total cost of sold stock: 7500.00 + 4500.00 = 12000.00000000
        $this->assertSame('12000.00000000', $activity['total_sell_cost']);
    }

    /**
     * 12. Realized profit aggregation is correct.
     */
    public function test_realized_profit_aggregation_is_correct(): void
    {
        // Buy 100 @ 100.00 (WAC: 100.00)
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');

        // Gain: Sell 20 @ 150.00 => Profit = 20 * 50 = +1000.00
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-01-10');
        // Loss: Sell 30 @ 80.00 => Profit = 30 * (-20) = -600.00
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '80.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA);

        // Net realized profit: 1000.00 + (-600.00) = 400.00000000
        $this->assertSame('400.00000000', $activity['total_realized_profit']);
    }

    /**
     * 13. Company-wise aggregation is correct.
     */
    public function test_company_wise_aggregation_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '25.00000000', '170.00000000', '2026-01-10');

        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '300.00000000', '2026-01-06');

        $report = $this->reportService->getCompanyWiseReport($this->userA);

        $this->assertCount(2, $report);

        // Apple is first alphabetically (AAPL)
        $aapl = $report[0];
        $this->assertSame('AAPL', $aapl['company']->symbol);
        $this->assertSame(1, $aapl['buy_transactions_count']);
        $this->assertSame(1, $aapl['sell_transactions_count']);
        $this->assertSame('100.00000000', $aapl['total_buy_quantity']);
        $this->assertSame('25.00000000', $aapl['total_sell_quantity']);
        $this->assertSame('15000.00000000', $aapl['total_buy_amount']);
        $this->assertSame('4250.00000000', $aapl['total_sell_amount']);
        $this->assertSame('500.00000000', $aapl['total_realized_profit']);
        $this->assertSame('75.00000000', $aapl['current_quantity']);
        $this->assertSame('11250.00000000', $aapl['current_inventory_cost']);

        // Microsoft is second (MSFT)
        $msft = $report[1];
        $this->assertSame('MSFT', $msft['company']->symbol);
        $this->assertSame(1, $msft['buy_transactions_count']);
        $this->assertSame(0, $msft['sell_transactions_count']);
        $this->assertSame('50.00000000', $msft['total_buy_quantity']);
        $this->assertSame('0.00000000', $msft['total_sell_quantity']);
        $this->assertSame('15000.00000000', $msft['total_buy_amount']);
        $this->assertSame('0.00000000', $msft['total_sell_amount']);
        $this->assertSame('0.00000000', $msft['total_realized_profit']);
        $this->assertSame('50.00000000', $msft['current_quantity']);
        $this->assertSame('15000.00000000', $msft['current_inventory_cost']);
    }

    /**
     * 14. Current position quantity comes from StockPosition.
     */
    public function test_current_position_quantity_comes_from_stock_position(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '40.00000000', '160.00000000', '2026-01-10');

        $position = StockPosition::where('user_id', $this->userA->id)
            ->where('company_id', $this->apple->id)
            ->first();

        $this->assertNotNull($position);
        $this->assertSame('60.00000000', (string) $position->quantity);

        $report = $this->reportService->getCompanyWiseReport($this->userA);
        $this->assertSame((string) $position->quantity, $report[0]['current_quantity']);
    }

    /**
     * 15. Current inventory cost comes from StockPosition.
     */
    public function test_current_inventory_cost_comes_from_stock_position(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '40.00000000', '160.00000000', '2026-01-10');

        $position = StockPosition::where('user_id', $this->userA->id)
            ->where('company_id', $this->apple->id)
            ->first();

        $this->assertNotNull($position);
        // 60 * 150.00 = 9000.00000000
        $this->assertSame('9000.00000000', (string) $position->inventory_amount);

        $report = $this->reportService->getCompanyWiseReport($this->userA);
        $this->assertSame((string) $position->inventory_amount, $report[0]['current_inventory_cost']);
    }

    /**
     * 16. Monthly grouping works.
     */
    public function test_monthly_grouping_works(): void
    {
        // January 2026
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-10');
        // February 2026
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '120.00000000', '2026-02-15');
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '130.00000000', '2026-02-20');

        $monthly = $this->reportService->getMonthlyReport($this->userA);

        $this->assertCount(2, $monthly);

        $jan = $monthly[0];
        $this->assertSame('2026-01', $jan['period_key']);
        $this->assertSame('January 2026', $jan['month_name']);
        $this->assertSame(1, $jan['buy_transactions_count']);
        $this->assertSame(0, $jan['sell_transactions_count']);
        $this->assertSame('100.00000000', $jan['total_buy_quantity']);

        $feb = $monthly[1];
        $this->assertSame('2026-02', $feb['period_key']);
        $this->assertSame('February 2026', $feb['month_name']);
        $this->assertSame(1, $feb['buy_transactions_count']);
        $this->assertSame(1, $feb['sell_transactions_count']);
        $this->assertSame('50.00000000', $feb['total_buy_quantity']);
        $this->assertSame('20.00000000', $feb['total_sell_quantity']);
    }

    /**
     * 17. Different years are not merged into the same month.
     */
    public function test_different_years_are_not_merged_into_the_same_month(): void
    {
        // January 2025
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2025-01-15');
        // January 2026
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-01-15');

        $monthly = $this->reportService->getMonthlyReport($this->userA);

        $this->assertCount(2, $monthly);

        $this->assertSame('2025-01', $monthly[0]['period_key']);
        $this->assertSame('January 2025', $monthly[0]['month_name']);
        $this->assertSame(2025, $monthly[0]['year']);
        $this->assertSame('10.00000000', $monthly[0]['total_buy_quantity']);

        $this->assertSame('2026-01', $monthly[1]['period_key']);
        $this->assertSame('January 2026', $monthly[1]['month_name']);
        $this->assertSame(2026, $monthly[1]['year']);
        $this->assertSame('20.00000000', $monthly[1]['total_buy_quantity']);
    }

    /**
     * 18. Annual grouping works.
     */
    public function test_annual_grouping_works(): void
    {
        // 2024
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2024-06-15');
        // 2025
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2025-06-15');
        // 2026
        $this->buyStock->execute($this->userA, $this->apple, '30.00000000', '140.00000000', '2026-06-15');
        $this->sellStock->execute($this->userA, $this->apple, '15.00000000', '160.00000000', '2026-07-20');

        $annual = $this->reportService->getAnnualReport($this->userA);

        $this->assertCount(3, $annual);

        $this->assertSame(2024, $annual[0]['year']);
        $this->assertSame('10.00000000', $annual[0]['total_buy_quantity']);
        $this->assertSame(0, $annual[0]['sell_transactions_count']);

        $this->assertSame(2025, $annual[1]['year']);
        $this->assertSame('20.00000000', $annual[1]['total_buy_quantity']);

        $this->assertSame(2026, $annual[2]['year']);
        $this->assertSame('30.00000000', $annual[2]['total_buy_quantity']);
        $this->assertSame('15.00000000', $annual[2]['total_sell_quantity']);
        $this->assertSame(1, $annual[2]['sell_transactions_count']);
    }

    /**
     * 19. Date-from filter works.
     */
    public function test_date_from_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-02-10');

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'date_from' => '2026-02-01',
        ]);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame('20.00000000', $activity['total_buy_quantity']);
    }

    /**
     * 20. Date-to filter works.
     */
    public function test_date_to_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-02-10');

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'date_to' => '2026-01-31',
        ]);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame('10.00000000', $activity['total_buy_quantity']);
    }

    /**
     * 21. Date range boundaries are inclusive.
     */
    public function test_date_range_boundaries_are_inclusive(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-01');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '110.00000000', '2026-01-15');
        $this->buyStock->execute($this->userA, $this->apple, '30.00000000', '120.00000000', '2026-01-31');
        $this->buyStock->execute($this->userA, $this->apple, '40.00000000', '130.00000000', '2026-02-01');

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
        ]);

        // Transactions on 2026-01-01, 2026-01-15, and 2026-01-31 are all included
        $this->assertSame(3, $activity['total_buy_transactions']);
        $this->assertSame('60.00000000', $activity['total_buy_quantity']);
    }

    /**
     * 22. Company filter works.
     */
    public function test_company_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '30.00000000', '250.00000000', '2026-01-12');

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'company_id' => $this->apple->id,
        ]);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame('50.00000000', $activity['total_buy_quantity']);

        $companyReport = $this->reportService->getCompanyWiseReport($this->userA, [
            'company_id' => $this->apple->id,
        ]);
        $this->assertCount(1, $companyReport);
        $this->assertSame('AAPL', $companyReport[0]['company']->symbol);
    }

    /**
     * 23. Exchange filter works.
     */
    public function test_exchange_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');     // NASDAQ
        $this->buyStock->execute($this->userA, $this->microsoft, '30.00000000', '250.00000000', '2026-01-12'); // NASDAQ
        $this->buyStock->execute($this->userA, $this->pfizer, '100.00000000', '30.00000000', '2026-01-15');    // NYSE

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'exchange' => 'NYSE',
        ]);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame('100.00000000', $activity['total_buy_quantity']);

        $companyReport = $this->reportService->getCompanyWiseReport($this->userA, [
            'exchange' => 'NYSE',
        ]);
        $this->assertCount(1, $companyReport);
        $this->assertSame('PFE', $companyReport[0]['company']->symbol);
    }

    /**
     * 24. Sector filter works.
     */
    public function test_sector_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');     // Technology
        $this->buyStock->execute($this->userA, $this->pfizer, '100.00000000', '30.00000000', '2026-01-15');    // Healthcare

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'sector' => 'Healthcare',
        ]);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame('100.00000000', $activity['total_buy_quantity']);

        $companyReport = $this->reportService->getCompanyWiseReport($this->userA, [
            'sector' => 'Technology',
        ]);
        $this->assertCount(1, $companyReport);
        $this->assertSame('AAPL', $companyReport[0]['company']->symbol);
    }

    /**
     * 25. BUY transaction-type filter works.
     */
    public function test_buy_transaction_type_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '170.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'type' => 'BUY',
        ]);

        $this->assertSame(1, $activity['total_buy_transactions']);
        $this->assertSame(0, $activity['total_sell_transactions']);
        $this->assertSame('100.00000000', $activity['total_buy_quantity']);
        $this->assertSame('0.00000000', $activity['total_sell_quantity']);

        $realized = $this->reportService->getRealizedProfitAnalysis($this->userA, [
            'type' => 'BUY',
        ]);
        $this->assertEmpty($realized);
    }

    /**
     * 26. SELL transaction-type filter works.
     */
    public function test_sell_transaction_type_filter_works(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '170.00000000', '2026-01-15');

        $activity = $this->reportService->getTransactionActivity($this->userA, [
            'type' => 'SELL',
        ]);

        $this->assertSame(0, $activity['total_buy_transactions']);
        $this->assertSame(1, $activity['total_sell_transactions']);
        $this->assertSame('0.00000000', $activity['total_buy_quantity']);
        $this->assertSame('30.00000000', $activity['total_sell_quantity']);
    }

    /**
     * 27. Combined filters work.
     */
    public function test_combined_filters_work(): void
    {
        // Apple in Jan 2026
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '40.00000000', '170.00000000', '2026-01-20');

        // Apple in Feb 2026
        $this->sellStock->execute($this->userA, $this->apple, '20.00000000', '180.00000000', '2026-02-10');

        // Pfizer in Jan 2026
        $this->buyStock->execute($this->userA, $this->pfizer, '200.00000000', '30.00000000', '2026-01-15');

        // Combined filter: AAPL + Jan 2026 + SELL
        $filters = [
            'date_from' => '2026-01-01',
            'date_to' => '2026-01-31',
            'company_id' => $this->apple->id,
            'type' => 'SELL',
        ];

        $activity = $this->reportService->getTransactionActivity($this->userA, $filters);
        $this->assertSame(0, $activity['total_buy_transactions']);
        $this->assertSame(1, $activity['total_sell_transactions']);
        $this->assertSame('40.00000000', $activity['total_sell_quantity']);

        $response = $this->actingAs($this->userA)->get(route('reports.index', $filters));
        $response->assertOk();
    }

    /**
     * 28. User isolation works.
     */
    public function test_user_isolation_works(): void
    {
        // User A buys Apple
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');

        // User B buys Microsoft
        $this->buyStock->execute($this->userB, $this->microsoft, '50.00000000', '300.00000000', '2026-01-12');

        $userAReport = $this->reportService->getCompanyWiseReport($this->userA);
        $userBReport = $this->reportService->getCompanyWiseReport($this->userB);

        $this->assertCount(1, $userAReport);
        $this->assertSame('AAPL', $userAReport[0]['company']->symbol);

        $this->assertCount(1, $userBReport);
        $this->assertSame('MSFT', $userBReport[0]['company']->symbol);
    }

    /**
     * 29. Another user's transactions never appear.
     */
    public function test_another_users_transactions_never_appear(): void
    {
        // User B buys 1000 Pfizer shares
        $this->buyStock->execute($this->userB, $this->pfizer, '1000.00000000', '50.00000000', '2026-01-10');
        $this->sellStock->execute($this->userB, $this->pfizer, '500.00000000', '70.00000000', '2026-01-15');

        // User A has no transactions
        $response = $this->actingAs($this->userA)->get(route('reports.index'));
        $response->assertOk();
        $response->assertDontSee('Pfizer Inc.');
        $response->assertDontSee('PFE');

        $activity = $this->reportService->getTransactionActivity($this->userA);
        $this->assertSame(0, $activity['total_transactions']);
        $this->assertSame('0.00000000', $activity['total_buy_quantity']);
    }

    /**
     * 30. Realized profit uses stored SELL transaction values.
     */
    public function test_realized_profit_uses_stored_sell_transaction_values(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        // Realized profit = 50 * (150 - 100) = 2500.00
        $sellTx = $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');

        $this->assertSame('2500.00000000', (string) $sellTx->realized_profit);

        $realizedAnalysis = $this->reportService->getRealizedProfitAnalysis($this->userA);
        $this->assertCount(1, $realizedAnalysis);
        $this->assertSame('2500.00000000', $realizedAnalysis[0]['realized_profit']);
        $this->assertSame('7500.00000000', $realizedAnalysis[0]['total_sell_revenue']);
        $this->assertSame('5000.00000000', $realizedAnalysis[0]['total_sell_cost_basis']);
    }

    /**
     * 31. Full liquidation is represented correctly.
     */
    public function test_full_liquidation_is_represented_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-05');
        // Fully liquidate: sell all 100 shares
        $this->sellStock->execute($this->userA, $this->apple, '100.00000000', '180.00000000', '2026-01-15');

        $summary = $this->reportService->getPortfolioSummary($this->userA);
        $this->assertSame('0.00000000', $summary['total_inventory_cost']);
        $this->assertSame('0.00000000', $summary['total_quantity']);
        $this->assertSame(0, $summary['active_positions_count']);
        $this->assertSame(1, $summary['total_positions_count']);
        $this->assertSame('3000.00000000', $summary['total_realized_profit']);

        $companyReport = $this->reportService->getCompanyWiseReport($this->userA);
        $this->assertCount(1, $companyReport);
        $this->assertSame('0.00000000', $companyReport[0]['current_quantity']);
        $this->assertSame('0.00000000', $companyReport[0]['current_inventory_cost']);
        $this->assertSame('3000.00000000', $companyReport[0]['total_realized_profit']);
    }

    /**
     * 32. Zero-quantity historical positions do not disappear from historical reports.
     */
    public function test_zero_quantity_historical_positions_do_not_disappear_from_historical_reports(): void
    {
        // Buy Apple and fully liquidate
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '110.00000000', '2026-01-10');

        // Buy Microsoft and keep active
        $this->buyStock->execute($this->userA, $this->microsoft, '20.00000000', '200.00000000', '2026-01-12');

        $companyReport = $this->reportService->getCompanyWiseReport($this->userA);

        // Both Apple (zero-quantity) and Microsoft (active) MUST be in the report
        $this->assertCount(2, $companyReport);

        $symbols = array_map(fn ($r) => $r['company']->symbol, $companyReport);
        $this->assertContains('AAPL', $symbols);
        $this->assertContains('MSFT', $symbols);

        $response = $this->actingAs($this->userA)->get(route('reports.index'));
        $response->assertOk();
        $response->assertSee('AAPL');
        $response->assertSee('MSFT');
    }

    /**
     * 33. Date range validation: invalid date or date_to before date_from fails validation.
     */
    public function test_date_range_validation_rejects_inverted_dates(): void
    {
        $response = $this->actingAs($this->userA)->get(route('reports.index', [
            'date_from' => '2026-02-15',
            'date_to' => '2026-01-10',
        ]));

        $response->assertSessionHasErrors('date_to');
    }
}
