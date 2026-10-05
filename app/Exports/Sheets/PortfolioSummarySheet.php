<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithFreezePane;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PortfolioSummarySheet implements FromArray, ShouldAutoSize, WithFreezePane, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array{
     *     total_inventory_cost: string,
     *     total_realized_profit: string,
     *     total_quantity: string,
     *     active_positions_count: int,
     *     total_positions_count: int,
     *     companies_held_count: int
     * }  $summary
     */
    public function __construct(
        protected array $summary
    ) {}

    public function title(): string
    {
        return 'Portfolio Summary';
    }

    public function freezePane(): string
    {
        return 'A2';
    }

    public function headings(): array
    {
        return [
            'Metric',
            'Value',
            'Description',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Inventory Cost Basis',
                (float) $this->summary['total_inventory_cost'],
                'Total acquisition cost basis for currently held shares (WAC)',
            ],
            [
                'Total Quantity',
                (float) $this->summary['total_quantity'],
                'Total shares currently in inventory',
            ],
            [
                'Active Positions',
                $this->summary['active_positions_count'],
                'Holdings with quantity greater than zero',
            ],
            [
                'Total Positions',
                $this->summary['total_positions_count'],
                'Total distinct positions recorded (active and closed)',
            ],
            [
                'Companies Held',
                $this->summary['companies_held_count'],
                'Distinct companies tracked in portfolio',
            ],
            [
                'Total Realized Profit / Loss',
                (float) $this->summary['total_realized_profit'],
                'Cumulative closed sales realized profit or loss',
            ],
        ];
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
