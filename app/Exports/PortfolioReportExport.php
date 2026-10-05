<?php

namespace App\Exports;

use App\Exports\Sheets\AnnualActivitySheet;
use App\Exports\Sheets\CompanySummarySheet;
use App\Exports\Sheets\MonthlyActivitySheet;
use App\Exports\Sheets\PortfolioSummarySheet;
use App\Exports\Sheets\RealizedProfitSheet;
use App\Exports\Sheets\TransactionActivitySheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PortfolioReportExport implements Export, WithMultipleSheets
{
    use Exportable;

    /**
     * @param  array<string, mixed>  $reportData
     */
    public function __construct(
        protected array $reportData
    ) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new PortfolioSummarySheet($this->reportData['portfolioSummary'] ?? []),
            new TransactionActivitySheet($this->reportData['transactionActivity'] ?? []),
            new CompanySummarySheet($this->reportData['companyWiseReport'] ?? []),
            new MonthlyActivitySheet($this->reportData['monthlyReport'] ?? []),
            new AnnualActivitySheet($this->reportData['annualReport'] ?? []),
            new RealizedProfitSheet($this->reportData['realizedProfitAnalysis'] ?? []),
        ];
    }
}
