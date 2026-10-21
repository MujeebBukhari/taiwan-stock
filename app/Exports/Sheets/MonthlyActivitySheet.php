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

class MonthlyActivitySheet implements FromArray, ShouldAutoSize, WithFreezePane, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  list<array{
     *     period_key: string,
     *     month_name: string,
     *     year: int,
     *     month: int,
     *     buy_transactions_count: int,
     *     sell_transactions_count: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_realized_profit: string
     * }>  $monthly
     */
    public function __construct(
        protected array $monthly
    ) {}

    public function title(): string
    {
        return 'Monthly Activity';
    }

    public function freezePane(): string
    {
        return 'A2';
    }

    public function headings(): array
    {
        $currency = Currency::getUserCurrency();

        return [
            'Month',
            'BUY Transactions',
            'SELL Transactions',
            'BUY Quantity',
            'SELL Quantity',
            'BUY Amount ('.$currency.')',
            'SELL Amount ('.$currency.')',
            'Realized Profit ('.$currency.')',
        ];
    }

    public function array(): array
    {
        $currency = Currency::getUserCurrency();
        $isUsd = $currency === Currency::USD;
        $rows = [];

        foreach ($this->monthly as $m) {
            $rows[] = [
                $m['month_name'],
                $m['buy_transactions_count'],
                $m['sell_transactions_count'],
                (float) $m['total_buy_quantity'],
                (float) $m['total_sell_quantity'],
                $isUsd ? (float) Currency::convertAmount($m['total_buy_amount'], Currency::TWD, Currency::USD) : (float) $m['total_buy_amount'],
                $isUsd ? (float) Currency::convertAmount($m['total_sell_amount'], Currency::TWD, Currency::USD) : (float) $m['total_sell_amount'],
                $isUsd ? (float) Currency::convertAmount($m['total_realized_profit'], Currency::TWD, Currency::USD) : (float) $m['total_realized_profit'],
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
