<?php

namespace App\Services\Reports;

use App\Exports\PortfolioReportExport;
use App\Models\Company;
use App\Models\User;
use App\Services\Portfolio\PortfolioReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExportService
{
    public function __construct(
        protected PortfolioReportService $reportService
    ) {}

    /**
     * Compile consistent report data across web view and exports.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function compileReportData(User $user, array $filters = []): array
    {
        return [
            'filters' => $filters,
            'portfolioSummary' => $this->reportService->getPortfolioSummary($user),
            'transactionActivity' => $this->reportService->getTransactionActivity($user, $filters),
            'companyWiseReport' => $this->reportService->getCompanyWiseReport($user, $filters),
            'monthlyReport' => $this->reportService->getMonthlyReport($user, $filters),
            'annualReport' => $this->reportService->getAnnualReport($user, $filters),
            'realizedProfitAnalysis' => $this->reportService->getRealizedProfitAnalysis($user, $filters),
        ];
    }

    /**
     * Generate standard downloadable filename based on date filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function generateFilename(string $extension, array $filters = []): string
    {
        $ext = ltrim($extension, '.');

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            return sprintf(
                'portfolio-report-%s-to-%s.%s',
                $filters['date_from'],
                $filters['date_to'],
                $ext
            );
        }

        return sprintf(
            'portfolio-report-%s.%s',
            now()->format('Y-m-d'),
            $ext
        );
    }

    /**
     * Export reports as an Excel workbook (.xlsx).
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportExcel(User $user, array $filters = []): BinaryFileResponse
    {
        $reportData = $this->compileReportData($user, $filters);
        $filename = $this->generateFilename('xlsx', $filters);

        return Excel::download(new PortfolioReportExport($reportData), $filename);
    }

    /**
     * Export reports as a formatted landscape PDF document (.pdf).
     *
     * @param  array<string, mixed>  $filters
     */
    public function exportPdf(User $user, array $filters = []): Response
    {
        $reportData = $this->compileReportData($user, $filters);
        $filename = $this->generateFilename('pdf', $filters);

        $selectedCompany = null;
        if (! empty($filters['company_id'])) {
            $selectedCompany = Company::find($filters['company_id']);
        }

        $pdf = Pdf::loadView('reports.pdf', array_merge($reportData, [
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'userName' => $user->name,
            'userEmail' => $user->email,
            'selectedCompany' => $selectedCompany,
        ]))->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
