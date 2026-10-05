<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Seed realistic publicly traded companies across NASDAQ, NYSE, and PSX.
     */
    public function run(): void
    {
        $companies = [
            // NASDAQ (Technology, Consumer, Communication)
            ['symbol' => 'AAPL', 'name' => 'Apple Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'MSFT', 'name' => 'Microsoft Corporation', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'NVDA', 'name' => 'NVIDIA Corporation', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'AMZN', 'name' => 'Amazon.com Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Consumer Discretionary', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'GOOGL', 'name' => 'Alphabet Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Communication Services', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'META', 'name' => 'Meta Platforms Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Communication Services', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'TSLA', 'name' => 'Tesla Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Consumer Discretionary', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'AVGO', 'name' => 'Broadcom Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'ASML', 'name' => 'ASML Holding N.V.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'AMD', 'name' => 'Advanced Micro Devices Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'NFLX', 'name' => 'Netflix Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Communication Services', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'ADBE', 'name' => 'Adobe Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'CSCO', 'name' => 'Cisco Systems Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'QCOM', 'name' => 'QUALCOMM Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'INTC', 'name' => 'Intel Corporation', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'COST', 'name' => 'Costco Wholesale Corporation', 'exchange' => 'NASDAQ', 'sector' => 'Consumer Staples', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'TXN', 'name' => 'Texas Instruments Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'PYPL', 'name' => 'PayPal Holdings Inc.', 'exchange' => 'NASDAQ', 'sector' => 'Financials', 'currency' => 'USD', 'is_active' => true],

            // NYSE (Conglomerate, Tech/Foundry, Financials, Healthcare, Energy, Industrials)
            ['symbol' => 'TSM', 'name' => 'Taiwan Semiconductor Manufacturing Co. (TSMC)', 'exchange' => 'NYSE', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'BRK.B', 'name' => 'Berkshire Hathaway Inc.', 'exchange' => 'NYSE', 'sector' => 'Financials', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'JPM', 'name' => 'JPMorgan Chase & Co.', 'exchange' => 'NYSE', 'sector' => 'Financials', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'V', 'name' => 'Visa Inc.', 'exchange' => 'NYSE', 'sector' => 'Financials', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'JNJ', 'name' => 'Johnson & Johnson', 'exchange' => 'NYSE', 'sector' => 'Healthcare', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'WMT', 'name' => 'Walmart Inc.', 'exchange' => 'NYSE', 'sector' => 'Consumer Staples', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'XOM', 'name' => 'Exxon Mobil Corporation', 'exchange' => 'NYSE', 'sector' => 'Energy', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'UNH', 'name' => 'UnitedHealth Group Inc.', 'exchange' => 'NYSE', 'sector' => 'Healthcare', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'LLY', 'name' => 'Eli Lilly and Company', 'exchange' => 'NYSE', 'sector' => 'Healthcare', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'PG', 'name' => 'The Procter & Gamble Company', 'exchange' => 'NYSE', 'sector' => 'Consumer Staples', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'MA', 'name' => 'Mastercard Incorporated', 'exchange' => 'NYSE', 'sector' => 'Financials', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'HD', 'name' => 'The Home Depot Inc.', 'exchange' => 'NYSE', 'sector' => 'Consumer Discretionary', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'CVX', 'name' => 'Chevron Corporation', 'exchange' => 'NYSE', 'sector' => 'Energy', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'KO', 'name' => 'The Coca-Cola Company', 'exchange' => 'NYSE', 'sector' => 'Consumer Staples', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'PEP', 'name' => 'PepsiCo Inc.', 'exchange' => 'NYSE', 'sector' => 'Consumer Staples', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'DIS', 'name' => 'The Walt Disney Company', 'exchange' => 'NYSE', 'sector' => 'Communication Services', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'IBM', 'name' => 'International Business Machines', 'exchange' => 'NYSE', 'sector' => 'Technology', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'BA', 'name' => 'The Boeing Company', 'exchange' => 'NYSE', 'sector' => 'Industrials', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'CAT', 'name' => 'Caterpillar Inc.', 'exchange' => 'NYSE', 'sector' => 'Industrials', 'currency' => 'USD', 'is_active' => true],
            ['symbol' => 'GE', 'name' => 'General Electric Aerospace', 'exchange' => 'NYSE', 'sector' => 'Industrials', 'currency' => 'USD', 'is_active' => true],

            // PSX (Pakistan Stock Exchange - Major Blue Chips & Sectors)
            ['symbol' => 'ENGRO', 'name' => 'Engro Corporation Limited', 'exchange' => 'PSX', 'sector' => 'Chemicals', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'OGDC', 'name' => 'Oil & Gas Development Company Limited', 'exchange' => 'PSX', 'sector' => 'Energy', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'LUCK', 'name' => 'Lucky Cement Limited', 'exchange' => 'PSX', 'sector' => 'Materials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'MCB', 'name' => 'MCB Bank Limited', 'exchange' => 'PSX', 'sector' => 'Financials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'HBL', 'name' => 'Habib Bank Limited', 'exchange' => 'PSX', 'sector' => 'Financials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'UBL', 'name' => 'United Bank Limited', 'exchange' => 'PSX', 'sector' => 'Financials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'MEBL', 'name' => 'Meezan Bank Limited', 'exchange' => 'PSX', 'sector' => 'Financials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'FFC', 'name' => 'Fauji Fertilizer Company Limited', 'exchange' => 'PSX', 'sector' => 'Chemicals', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'EFERT', 'name' => 'Engro Fertilizers Limited', 'exchange' => 'PSX', 'sector' => 'Chemicals', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'SYS', 'name' => 'Systems Limited', 'exchange' => 'PSX', 'sector' => 'Technology', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'PPL', 'name' => 'Pakistan Petroleum Limited', 'exchange' => 'PSX', 'sector' => 'Energy', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'POL', 'name' => 'Pakistan Oilfields Limited', 'exchange' => 'PSX', 'sector' => 'Energy', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'HUBC', 'name' => 'The Hub Power Company Limited', 'exchange' => 'PSX', 'sector' => 'Utilities', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'PSO', 'name' => 'Pakistan State Oil Company Limited', 'exchange' => 'PSX', 'sector' => 'Energy', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'MARI', 'name' => 'Mari Energies Limited', 'exchange' => 'PSX', 'sector' => 'Energy', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'SEARL', 'name' => 'The Searle Company Limited', 'exchange' => 'PSX', 'sector' => 'Healthcare', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'DGKC', 'name' => 'D.G. Khan Cement Company Limited', 'exchange' => 'PSX', 'sector' => 'Materials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'KAPCO', 'name' => 'Kot Addu Power Company Limited', 'exchange' => 'PSX', 'sector' => 'Utilities', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'MTL', 'name' => 'Millat Tractors Limited', 'exchange' => 'PSX', 'sector' => 'Industrials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'BAHL', 'name' => 'Bank AL Habib Limited', 'exchange' => 'PSX', 'sector' => 'Financials', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'INDU', 'name' => 'Indus Motor Company Limited', 'exchange' => 'PSX', 'sector' => 'Consumer Discretionary', 'currency' => 'PKR', 'is_active' => true],
            ['symbol' => 'TRG', 'name' => 'TRG Pakistan Limited', 'exchange' => 'PSX', 'sector' => 'Technology', 'currency' => 'PKR', 'is_active' => true],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(
                ['symbol' => $company['symbol']],
                $company
            );
        }
    }
}
