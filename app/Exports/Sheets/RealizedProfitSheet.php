<?php

namespace App\Exports\Sheets;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithFreezePane;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RealizedProfitSheet implements FromArray, ShouldAutoSize, WithFreezePane, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  list<array{
     *     company: Company,
     *     total_sell_revenue: string,
     *     total_sell_cost_basis: string,
     *     realized_profit: string,
     *     sell_transactions_count: int
     * }>  $realizedProfit
     */
    public function __construct(
        protected array $realizedProfit
    ) {}

    public function title(): string
    {
        return 'Realized Profit Analysis';
    }

    public function freezePane(): string
    {
        return 'A2';
    }

    public function headings(): array
    {
        return [
            'Company',
            'Symbol',
            'Currency',
            'Closed Orders',
            'Total SELL Revenue',
            'Total SELL Cost Basis',
            'Realized Profit / Loss',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->realizedProfit as $rp) {
            $rows[] = [
                $rp['company']->name,
                $rp['company']->symbol,
                $rp['company']->currency,
                $rp['sell_transactions_count'],
                (float) $rp['total_sell_revenue'],
                (float) $rp['total_sell_cost_basis'],
                (float) $rp['realized_profit'],
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'],
                ],
            ],
        ];
    }
}
