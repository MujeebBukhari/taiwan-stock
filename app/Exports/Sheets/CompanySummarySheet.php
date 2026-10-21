<?php

namespace App\Exports\Sheets;

use App\Models\Company;
use App\Support\Currency;
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
        $currency = Currency::getUserCurrency();

        return [
            'Company',
            'Symbol',
            'Exchange',
            'BUY Transactions',
            'SELL Transactions',
            'Total BUY Quantity',
            'Total SELL Quantity',
            'Total BUY Amount ('.$currency.')',
            'Total SELL Amount ('.$currency.')',
            'Realized Profit ('.$currency.')',
            'Current Quantity',
            'Current Inventory Cost Basis ('.$currency.')',
        ];
    }

    public function array(): array
    {
        $currency = Currency::getUserCurrency();
        $isUsd = $currency === Currency::USD;
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
                $isUsd ? (float) Currency::convertAmount($item['total_buy_amount'], Currency::TWD, Currency::USD) : (float) $item['total_buy_amount'],
                $isUsd ? (float) Currency::convertAmount($item['total_sell_amount'], Currency::TWD, Currency::USD) : (float) $item['total_sell_amount'],
                $isUsd ? (float) Currency::convertAmount($item['total_realized_profit'], Currency::TWD, Currency::USD) : (float) $item['total_realized_profit'],
                (float) $item['current_quantity'],
                $isUsd ? (float) Currency::convertAmount($item['current_inventory_cost'], Currency::TWD, Currency::USD) : (float) $item['current_inventory_cost'],
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
