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
            // TWSE (Taiwan Stock Exchange - Primary Market)
            ['symbol' => '2330', 'name' => 'Taiwan Semiconductor Manufacturing Co. (TSMC)', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '980.00', 'is_active' => true],
            ['symbol' => '2317', 'name' => 'Hon Hai Precision Industry (Foxconn)', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '185.00', 'is_active' => true],
            ['symbol' => '2454', 'name' => 'MediaTek Inc.', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '1250.00', 'is_active' => true],
            ['symbol' => '2412', 'name' => 'Chunghwa Telecom Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Communication Services', 'currency' => 'TWD', 'last_price' => '125.00', 'is_active' => true],
            ['symbol' => '2881', 'name' => 'Fubon Financial Holding Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Financials', 'currency' => 'TWD', 'last_price' => '88.50', 'is_active' => true],
            ['symbol' => '2882', 'name' => 'Cathay Financial Holding Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Financials', 'currency' => 'TWD', 'last_price' => '65.00', 'is_active' => true],
            ['symbol' => '2308', 'name' => 'Delta Electronics, Inc.', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '390.00', 'is_active' => true],
            ['symbol' => '2891', 'name' => 'CTBC Financial Holding Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Financials', 'currency' => 'TWD', 'last_price' => '36.50', 'is_active' => true],
            ['symbol' => '1301', 'name' => 'Formosa Plastics Corporation', 'exchange' => 'TWSE', 'sector' => 'Materials', 'currency' => 'TWD', 'last_price' => '50.00', 'is_active' => true],
            ['symbol' => '2002', 'name' => 'China Steel Corporation', 'exchange' => 'TWSE', 'sector' => 'Materials', 'currency' => 'TWD', 'last_price' => '22.00', 'is_active' => true],
            ['symbol' => '2382', 'name' => 'Quanta Computer Inc.', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '270.00', 'is_active' => true],
            ['symbol' => '2357', 'name' => 'ASUSTeK Computer Inc.', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '540.00', 'is_active' => true],
            ['symbol' => '3008', 'name' => 'Largan Precision Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '2500.00', 'is_active' => true],
            ['symbol' => '3711', 'name' => 'ASE Technology Holding Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '155.00', 'is_active' => true],
            ['symbol' => '1216', 'name' => 'Uni-President Enterprises Corp.', 'exchange' => 'TWSE', 'sector' => 'Consumer Staples', 'currency' => 'TWD', 'last_price' => '82.00', 'is_active' => true],
            ['symbol' => '2886', 'name' => 'Mega Financial Holding Co., Ltd.', 'exchange' => 'TWSE', 'sector' => 'Financials', 'currency' => 'TWD', 'last_price' => '39.00', 'is_active' => true],

            // TPEx (Taipei Exchange - Taiwan Secondary / OTC Market)
            ['symbol' => '5347', 'name' => 'Vanguard International Semiconductor Corp.', 'exchange' => 'TPEx', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '102.00', 'is_active' => true],
            ['symbol' => '3105', 'name' => 'Win Semiconductors Corp.', 'exchange' => 'TPEx', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '128.00', 'is_active' => true],
            ['symbol' => '8299', 'name' => 'Phison Electronics Corp.', 'exchange' => 'TPEx', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '530.00', 'is_active' => true],
            ['symbol' => '6488', 'name' => 'GlobalWafers Co., Ltd.', 'exchange' => 'TPEx', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '420.00', 'is_active' => true],
            ['symbol' => '5483', 'name' => 'Sino-American Silicon Products Inc.', 'exchange' => 'TPEx', 'sector' => 'Technology', 'currency' => 'TWD', 'last_price' => '145.00', 'is_active' => true],

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
