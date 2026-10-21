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

class TransactionActivitySheet implements FromArray, ShouldAutoSize, WithFreezePane, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array{
     *     total_buy_transactions: int,
     *     total_sell_transactions: int,
     *     total_transactions: int,
     *     total_buy_quantity: string,
     *     total_sell_quantity: string,
     *     total_buy_amount: string,
     *     total_sell_amount: string,
     *     total_sell_cost: string,
     *     total_realized_profit: string
     * }  $activity
     */
    public function __construct(
        protected array $activity
    ) {}

    public function title(): string
    {
        return 'Transaction Activity';
    }

    public function freezePane(): string
    {
        return 'A2';
    }

    public function headings(): array
    {
        return [
            'Activity Metric',
            'Value',
        ];
    }

    public function array(): array
    {
        $currency = Currency::getUserCurrency();
        $isUsd = $currency === Currency::USD;

        $buyAmount = $isUsd
            ? (float) Currency::convertAmount($this->activity['total_buy_amount'], Currency::TWD, Currency::USD)
            : (float) $this->activity['total_buy_amount'];

        $sellAmount = $isUsd
            ? (float) Currency::convertAmount($this->activity['total_sell_amount'], Currency::TWD, Currency::USD)
            : (float) $this->activity['total_sell_amount'];

        $sellCost = $isUsd
            ? (float) Currency::convertAmount($this->activity['total_sell_cost'], Currency::TWD, Currency::USD)
            : (float) $this->activity['total_sell_cost'];

        $profit = $isUsd
            ? (float) Currency::convertAmount($this->activity['total_realized_profit'], Currency::TWD, Currency::USD)
            : (float) $this->activity['total_realized_profit'];

        return [
            ['Total BUY Transactions', $this->activity['total_buy_transactions']],
            ['Total SELL Transactions', $this->activity['total_sell_transactions']],
            ['Total Transactions', $this->activity['total_transactions']],
            ['Total BUY Quantity', (float) $this->activity['total_buy_quantity']],
            ['Total SELL Quantity', (float) $this->activity['total_sell_quantity']],
            ['Total BUY Amount', $buyAmount],
            ['Total SELL Amount', $sellAmount],
            ['Total SELL Cost Basis', $sellCost],
            ['Total Realized Profit / Loss', $profit],
            ['Display Currency', $currency],
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
