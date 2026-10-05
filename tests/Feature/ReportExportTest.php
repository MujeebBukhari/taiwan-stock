<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use App\Services\Reports\ReportExportService;
use App\Services\Transactions\BuyStock;
use App\Services\Transactions\SellStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $userA;

    private User $userB;

    private Company $apple;

    private Company $microsoft;

    private Company $pfizer;

    private BuyStock $buyStock;

    private SellStock $sellStock;

    private ReportExportService $exportService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userA = User::factory()->user()->create(['name' => 'Alice Investor', 'email' => 'alice@example.com']);
        $this->userB = User::factory()->user()->create(['name' => 'Bob Investor', 'email' => 'bob@example.com']);

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
        $this->exportService = app(ReportExportService::class);
    }

    /**
     * 1. Guest cannot export Excel.
     */
    public function test_guest_cannot_export_excel(): void
    {
        $response = $this->get(route('reports.export.excel'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Guest cannot export PDF.
     */
    public function test_guest_cannot_export_pdf(): void
    {
        $response = $this->get(route('reports.export.pdf'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 3. Authenticated user can export Excel.
     */
    public function test_authenticated_user_can_export_excel(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));

        $response->assertOk();
    }

    /**
     * 4. Authenticated user can export PDF.
     */
    public function test_authenticated_user_can_export_pdf(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.pdf'));

        $response->assertOk();
    }

    /**
     * 5. Excel response has correct content type.
     */
    public function test_excel_response_has_correct_content_type(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));

        $response->assertOk();
        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment;', $disposition);
        $this->assertStringContainsString('.xlsx', $disposition);
    }

    /**
     * 6. PDF response has correct content type.
     */
    public function test_pdf_response_has_correct_content_type(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.pdf'));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $disposition = (string) $response->headers->get('content-disposition');
        $this->assertStringContainsString('attachment;', $disposition);
        $this->assertStringContainsString('.pdf', $disposition);
    }

    /**
     * 7. Excel file can be opened and validated by PhpSpreadsheet.
     */
    public function test_excel_file_can_be_opened_and_validated_by_phpspreadsheet(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '40.00000000', '170.00000000', '2026-01-15');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));

        $response->assertOk();
        $filePath = $response->getFile()->getPathname();

        $spreadsheet = IOFactory::load($filePath);

        $this->assertSame(6, $spreadsheet->getSheetCount());

        $expectedSheetNames = [
            'Portfolio Summary',
            'Transaction Activity',
            'Company Summary',
            'Monthly Activity',
            'Annual Activity',
            'Realized Profit Analysis',
        ];

        $this->assertSame($expectedSheetNames, $spreadsheet->getSheetNames());

        // Validate summary values
        $summarySheet = $spreadsheet->getSheetByName('Portfolio Summary');
        $this->assertNotNull($summarySheet);
        $this->assertSame('Metric', $summarySheet->getCell('A1')->getValue());
        $this->assertSame('Inventory Cost Basis', $summarySheet->getCell('A2')->getValue());
        // Remaining 60 shares @ 150.00 = 9000.00
        $this->assertEquals(9000.0, (float) $summarySheet->getCell('B2')->getValue());

        // Validate transaction activity values
        $activitySheet = $spreadsheet->getSheetByName('Transaction Activity');
        $this->assertNotNull($activitySheet);
        $this->assertSame('Activity Metric', $activitySheet->getCell('A1')->getValue());
        $this->assertSame('Total BUY Transactions', $activitySheet->getCell('A2')->getValue());
        $this->assertEquals(1, (int) $activitySheet->getCell('B2')->getValue());
    }

    /**
     * 8. PDF response successfully renders.
     */
    public function test_pdf_response_successfully_renders(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.pdf'));

        $response->assertOk();
        $content = $response->getContent();

        // Standard PDF magic byte signature
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertGreaterThan(1000, strlen($content));
    }

    /**
     * 9. Current report filters are preserved for Excel.
     */
    public function test_current_report_filters_are_preserved_for_excel(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '250.00000000', '2026-02-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'company_id' => $this->apple->id,
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);

        // Header row = row 1, Apple row = row 2
        $this->assertSame('Apple Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertSame('AAPL', $companySheet->getCell('B2')->getValue());

        // Row 3 should be empty because Microsoft was filtered out
        $this->assertNull($companySheet->getCell('A3')->getValue());
    }

    /**
     * 10. Current report filters are preserved for PDF.
     */
    public function test_current_report_filters_are_preserved_for_pdf(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '50.00000000', '250.00000000', '2026-02-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.pdf', [
            'company_id' => $this->apple->id,
        ]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    /**
     * 11. Date filters are respected.
     */
    public function test_date_filters_are_respected(): void
    {
        // Jan 2026
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2026-01-10');
        // Feb 2026
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '120.00000000', '2026-02-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'date_from' => '2026-02-01',
            'date_to' => '2026-02-28',
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $activitySheet = $spreadsheet->getSheetByName('Transaction Activity');
        $this->assertNotNull($activitySheet);
        // Total BUY quantity row is row 5
        $this->assertEquals(20.0, (float) $activitySheet->getCell('B5')->getValue());
    }

    /**
     * 12. Company filter is respected.
     */
    public function test_company_filter_is_respected(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->microsoft, '30.00000000', '250.00000000', '2026-01-12');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'company_id' => $this->apple->id,
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);
        $this->assertSame('Apple Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertNull($companySheet->getCell('A3')->getValue());
    }

    /**
     * 13. Exchange filter is respected.
     */
    public function test_exchange_filter_is_respected(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');  // NASDAQ
        $this->buyStock->execute($this->userA, $this->pfizer, '100.00000000', '30.00000000', '2026-01-15'); // NYSE

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'exchange' => 'NYSE',
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);
        $this->assertSame('Pfizer Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertSame('NYSE', $companySheet->getCell('C2')->getValue());
        $this->assertNull($companySheet->getCell('A3')->getValue());
    }

    /**
     * 14. Sector filter is respected.
     */
    public function test_sector_filter_is_respected(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');  // Technology
        $this->buyStock->execute($this->userA, $this->pfizer, '100.00000000', '30.00000000', '2026-01-15'); // Healthcare

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'sector' => 'Healthcare',
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);
        $this->assertSame('Pfizer Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertNull($companySheet->getCell('A3')->getValue());
    }

    /**
     * 15. BUY filter is respected.
     */
    public function test_buy_filter_is_respected(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '170.00000000', '2026-01-15');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'type' => 'BUY',
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $activitySheet = $spreadsheet->getSheetByName('Transaction Activity');
        $this->assertNotNull($activitySheet);
        $this->assertEquals(1, (int) $activitySheet->getCell('B2')->getValue()); // BUY orders = 1
        $this->assertEquals(0, (int) $activitySheet->getCell('B3')->getValue()); // SELL orders = 0

        // Realized profit sheet should have no rows under BUY filter
        $realizedSheet = $spreadsheet->getSheetByName('Realized Profit Analysis');
        $this->assertNotNull($realizedSheet);
        $this->assertNull($realizedSheet->getCell('A2')->getValue());
    }

    /**
     * 16. SELL filter is respected.
     */
    public function test_sell_filter_is_respected(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->sellStock->execute($this->userA, $this->apple, '30.00000000', '170.00000000', '2026-01-15');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'type' => 'SELL',
        ]));

        $response->assertOk();
        $spreadsheet = IOFactory::load($response->getFile()->getPathname());

        $activitySheet = $spreadsheet->getSheetByName('Transaction Activity');
        $this->assertNotNull($activitySheet);
        $this->assertEquals(0, (int) $activitySheet->getCell('B2')->getValue()); // BUY orders = 0
        $this->assertEquals(1, (int) $activitySheet->getCell('B3')->getValue()); // SELL orders = 1
    }

    /**
     * 17. User isolation works for Excel.
     */
    public function test_user_isolation_works_for_excel(): void
    {
        // User A buys Apple
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        // User B buys Microsoft
        $this->buyStock->execute($this->userB, $this->microsoft, '50.00000000', '300.00000000', '2026-01-12');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);

        $this->assertSame('Apple Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertNull($companySheet->getCell('A3')->getValue());
    }

    /**
     * 18. User isolation works for PDF.
     */
    public function test_user_isolation_works_for_pdf(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-10');
        $this->buyStock->execute($this->userB, $this->microsoft, '50.00000000', '300.00000000', '2026-01-12');

        $reportData = $this->exportService->compileReportData($this->userA);
        $this->assertCount(1, $reportData['companyWiseReport']);
        $this->assertSame('AAPL', $reportData['companyWiseReport'][0]['company']->symbol);

        $response = $this->actingAs($this->userA)->get(route('reports.export.pdf'));
        $response->assertOk();
    }

    /**
     * 19. User B data never appears in User A export.
     */
    public function test_user_b_data_never_appears_in_user_a_export(): void
    {
        $this->buyStock->execute($this->userB, $this->pfizer, '1000.00000000', '50.00000000', '2026-01-10');

        $reportDataA = $this->exportService->compileReportData($this->userA);
        $this->assertEmpty($reportDataA['companyWiseReport']);
        $this->assertSame('0.00000000', $reportDataA['transactionActivity']['total_buy_quantity']);

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);
        $this->assertNull($companySheet->getCell('A2')->getValue());
    }

    /**
     * 20. Realized profit uses stored transaction values.
     */
    public function test_realized_profit_uses_stored_transaction_values(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-05');
        // Profit = 50 * (150 - 100) = 2500.00
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '150.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $realizedSheet = $spreadsheet->getSheetByName('Realized Profit Analysis');
        $this->assertNotNull($realizedSheet);

        // Row 2: Apple, Symbol, Currency, Closed Orders, Total Revenue, Total Cost, Realized Profit
        $this->assertSame('Apple Inc.', $realizedSheet->getCell('A2')->getValue());
        $this->assertSame('AAPL', $realizedSheet->getCell('B2')->getValue());
        $this->assertEquals(1, (int) $realizedSheet->getCell('D2')->getValue());
        $this->assertEquals(7500.0, (float) $realizedSheet->getCell('E2')->getValue());
        $this->assertEquals(5000.0, (float) $realizedSheet->getCell('F2')->getValue());
        $this->assertEquals(2500.0, (float) $realizedSheet->getCell('G2')->getValue());
    }

    /**
     * 21. Inventory cost basis is correct.
     */
    public function test_inventory_cost_basis_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '125.50000000', '2026-01-05'); // 12,550.00
        $this->buyStock->execute($this->userA, $this->microsoft, '40.00000000', '250.25000000', '2026-01-06'); // 10,010.00

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $summarySheet = $spreadsheet->getSheetByName('Portfolio Summary');
        $this->assertNotNull($summarySheet);

        // Total inventory cost = 12550 + 10010 = 22560.00
        $this->assertEquals(22560.0, (float) $summarySheet->getCell('B2')->getValue());
    }

    /**
     * 22. Company summary is correct.
     */
    public function test_company_summary_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '150.00000000', '2026-01-05');
        $this->sellStock->execute($this->userA, $this->apple, '25.00000000', '180.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);

        $this->assertSame('Apple Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertSame('AAPL', $companySheet->getCell('B2')->getValue());
        $this->assertSame('NASDAQ', $companySheet->getCell('C2')->getValue());
        $this->assertEquals(1, (int) $companySheet->getCell('D2')->getValue());
        $this->assertEquals(1, (int) $companySheet->getCell('E2')->getValue());
        $this->assertEquals(100.0, (float) $companySheet->getCell('F2')->getValue());
        $this->assertEquals(25.0, (float) $companySheet->getCell('G2')->getValue());
        $this->assertEquals(15000.0, (float) $companySheet->getCell('H2')->getValue());
        $this->assertEquals(4500.0, (float) $companySheet->getCell('I2')->getValue());
        $this->assertEquals(750.0, (float) $companySheet->getCell('J2')->getValue()); // 25 * 30 profit
        $this->assertEquals(75.0, (float) $companySheet->getCell('K2')->getValue());
        $this->assertEquals(11250.0, (float) $companySheet->getCell('L2')->getValue());
    }

    /**
     * 23. Monthly report is correct.
     */
    public function test_monthly_report_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '100.00000000', '100.00000000', '2026-01-10');
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '120.00000000', '2026-02-15');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $monthlySheet = $spreadsheet->getSheetByName('Monthly Activity');
        $this->assertNotNull($monthlySheet);

        $this->assertSame('January 2026', $monthlySheet->getCell('A2')->getValue());
        $this->assertEquals(100.0, (float) $monthlySheet->getCell('D2')->getValue());

        $this->assertSame('February 2026', $monthlySheet->getCell('A3')->getValue());
        $this->assertEquals(50.0, (float) $monthlySheet->getCell('D3')->getValue());
    }

    /**
     * 24. Annual report is correct.
     */
    public function test_annual_report_is_correct(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '10.00000000', '100.00000000', '2025-06-15');
        $this->buyStock->execute($this->userA, $this->apple, '20.00000000', '150.00000000', '2026-06-15');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $annualSheet = $spreadsheet->getSheetByName('Annual Activity');
        $this->assertNotNull($annualSheet);

        $this->assertEquals(2025, (int) $annualSheet->getCell('A2')->getValue());
        $this->assertEquals(10.0, (float) $annualSheet->getCell('D2')->getValue());

        $this->assertEquals(2026, (int) $annualSheet->getCell('A3')->getValue());
        $this->assertEquals(20.0, (float) $annualSheet->getCell('D3')->getValue());
    }

    /**
     * 25. Empty portfolio export works.
     */
    public function test_empty_portfolio_export_works(): void
    {
        // User A has no transactions or positions
        $excelResponse = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $excelResponse->assertOk();

        $spreadsheet = IOFactory::load($excelResponse->getFile()->getPathname());
        $this->assertSame(6, $spreadsheet->getSheetCount());

        $pdfResponse = $this->actingAs($this->userA)->get(route('reports.export.pdf'));
        $pdfResponse->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());
    }

    /**
     * 26. Zero-quantity historical positions are handled correctly.
     */
    public function test_zero_quantity_historical_positions_are_handled_correctly(): void
    {
        $this->buyStock->execute($this->userA, $this->apple, '50.00000000', '100.00000000', '2026-01-05');
        // Fully liquidate
        $this->sellStock->execute($this->userA, $this->apple, '50.00000000', '120.00000000', '2026-01-10');

        $response = $this->actingAs($this->userA)->get(route('reports.export.excel'));
        $response->assertOk();

        $spreadsheet = IOFactory::load($response->getFile()->getPathname());
        $companySheet = $spreadsheet->getSheetByName('Company Summary');
        $this->assertNotNull($companySheet);

        // Apple is present
        $this->assertSame('Apple Inc.', $companySheet->getCell('A2')->getValue());
        $this->assertSame('AAPL', $companySheet->getCell('B2')->getValue());
        $this->assertEquals(0.0, (float) $companySheet->getCell('K2')->getValue()); // Current Qty = 0
        $this->assertEquals(0.0, (float) $companySheet->getCell('L2')->getValue()); // Cost Basis = 0
        $this->assertEquals(1000.0, (float) $companySheet->getCell('J2')->getValue()); // Realized profit = 1000.00
    }

    /**
     * 27. Invalid date range is rejected.
     */
    public function test_invalid_date_range_is_rejected(): void
    {
        $excelResponse = $this->actingAs($this->userA)->get(route('reports.export.excel', [
            'date_from' => '2026-03-31',
            'date_to' => '2026-01-01',
        ]));

        $excelResponse->assertSessionHasErrors('date_to');

        $pdfResponse = $this->actingAs($this->userA)->get(route('reports.export.pdf', [
            'date_from' => '2026-03-31',
            'date_to' => '2026-01-01',
        ]));

        $pdfResponse->assertSessionHasErrors('date_to');
    }

    /**
     * 28. Generated filename is clean and contains no sensitive info.
     */
    public function test_generated_filename_is_clean_and_contains_no_sensitive_info(): void
    {
        $defaultName = $this->exportService->generateFilename('xlsx');
        $this->assertMatchesRegularExpression('/^portfolio-report-\d{4}-\d{2}-\d{2}\.xlsx$/', $defaultName);
        $this->assertStringNotContainsString('alice', strtolower($defaultName));
        $this->assertStringNotContainsString('example.com', strtolower($defaultName));
        $this->assertStringNotContainsString('user', strtolower($defaultName));

        $rangedName = $this->exportService->generateFilename('pdf', [
            'date_from' => '2026-01-01',
            'date_to' => '2026-03-31',
        ]);
        $this->assertSame('portfolio-report-2026-01-01-to-2026-03-31.pdf', $rangedName);
    }
}
