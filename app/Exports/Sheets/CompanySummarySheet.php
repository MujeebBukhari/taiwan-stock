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

class CompanySummarySheet implements FromArray, ShouldAutoSize, WithFreezePane, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  list<array{
     *     company: Company,
     *     buy_transactions_count: int,
     *     sell_transactions_count: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_realized_profit: string,
     *     current_quantity: string,
     *     current_inventory_cost: string,
     *     current_wac: string
     * }>  $companyWise
     */
    public function __construct(
        protected array $companyWise
    ) {}

    public function title(): string
    {
        return 'Company Summary';
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
            'Exchange',
            'BUY Transactions',
            'SELL Transactions',
            'Total BUY Quantity',
            'Total SELL Quantity',
            'Total BUY Amount',
            'Total SELL Amount',
            'Realized Profit',
            'Current Quantity',
            'Current Inventory Cost Basis',
        ];
    }

    public function array(): array
    {
        $rows = [];

        foreach ($this->companyWise as $item) {
            $rows[] = [
                $item['company']->name,
                $item['company']->symbol,
                $item['company']->exchange,
                $item['buy_transactions_count'],
                $item['sell_transactions_count'],
                (float) $item['total_buy_quantity'],
                (float) $item['total_sell_quantity'],
                (float) $item['total_buy_amount'],
                (float) $item['total_sell_amount'],
                (float) $item['total_realized_profit'],
                (float) $item['current_quantity'],
                (float) $item['current_inventory_cost'],
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
