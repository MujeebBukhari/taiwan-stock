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
        $currency = Currency::getUserCurrency();

        return [
            'Company',
            'Symbol',
            'Currency',
            'Closed Orders',
            'Total SELL Revenue ('.$currency.')',
            'Total SELL Cost Basis ('.$currency.')',
            'Realized Profit / Loss ('.$currency.')',
        ];
    }

    public function array(): array
    {
        $currency = Currency::getUserCurrency();
        $isUsd = $currency === Currency::USD;
        $rows = [];

        foreach ($this->realizedProfit as $rp) {
            $rows[] = [
                $rp['company']->name,
                $rp['company']->symbol,
                $rp['company']->currency,
                $rp['sell_transactions_count'],
                $isUsd ? (float) Currency::convertAmount($rp['total_sell_revenue'], Currency::TWD, Currency::USD) : (float) $rp['total_sell_revenue'],
                $isUsd ? (float) Currency::convertAmount($rp['total_sell_cost_basis'], Currency::TWD, Currency::USD) : (float) $rp['total_sell_cost_basis'],
                $isUsd ? (float) Currency::convertAmount($rp['realized_profit'], Currency::TWD, Currency::USD) : (float) $rp['realized_profit'],
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
