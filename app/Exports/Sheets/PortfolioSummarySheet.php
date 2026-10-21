<?php

namespace App\Exports\Sheets;

use App\Support\Currency;
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
        $currency = Currency::getUserCurrency();
        $isUsd = $currency === Currency::USD;

        $invCost = $isUsd
            ? (float) Currency::convertAmount($this->summary['total_inventory_cost'], Currency::TWD, Currency::USD)
            : (float) $this->summary['total_inventory_cost'];

        $profit = $isUsd
            ? (float) Currency::convertAmount($this->summary['total_realized_profit'], Currency::TWD, Currency::USD)
            : (float) $this->summary['total_realized_profit'];

        return [
            [
                'Inventory Cost Basis',
                $invCost,
                'Total acquisition cost basis for currently held shares (WAC)'.($isUsd ? ' [USD]' : ' [TWD]'),
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
                $profit,
                'Cumulative closed sales realized profit or loss'.($isUsd ? ' [USD]' : ' [TWD]'),
            ],
            [
                'Display Currency',
                $currency,
                $isUsd
                    ? 'Converted from TWD (1 TWD = '.(Currency::getRate(Currency::TWD, Currency::USD) ?? '0.03125').' USD)'
                    : 'Primary Accounting Currency (TWD)',
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
