<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Portfolio Report</title>
    <style>
        @page {
            margin: 20px 25px;
            size: a4 landscape;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #1e293b;
            line-height: 1.35;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .title {
            font-size: 16pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .subtitle {
            font-size: 8pt;
            color: #64748b;
            margin-top: 3px;
        }

        .meta-text {
            font-size: 8pt;
            color: #475569;
            text-align: right;
        }

        .filters-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 14px;
            font-size: 8pt;
        }

        .filter-chip {
            display: inline-block;
            background-color: #e2e8f0;
            color: #334155;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            margin-right: 8px;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            border-left: 3px solid #059669;
            padding-left: 6px;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }

        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            font-size: 7.5pt;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: 'Courier New', Courier, monospace;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-success {
            color: #059669;
            font-weight: bold;
        }

        .text-danger {
            color: #dc2626;
            font-weight: bold;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-card {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 6px 8px;
            text-align: left;
            width: 16.66%;
        }

        .summary-card-label {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-card-val {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 2px;
            font-family: 'Courier New', Courier, monospace;
        }

        .empty-state {
            text-align: center;
            padding: 10px;
            color: #64748b;
            font-style: italic;
        }

        .footer {
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="title">Portfolio Report</h1>
                <p class="subtitle">Historical Ledger Accounting &amp; Performance Analytics (Cost Basis Mode &bull; WAC)</p>
            </td>
            <td class="meta-text" style="vertical-align: top;">
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>User:</strong> {{ $userName }} ({{ $userEmail }})</div>
            </td>
        </tr>
    </table>

    <!-- Applied Filters (Only display filters actually selected) -->
    @php
        $activeFilters = [];
        if (!empty($filters['date_from'])) {
            $activeFilters['From Date'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $activeFilters['To Date'] = $filters['date_to'];
        }
        if (!empty($filters['company_id']) && isset($selectedCompany)) {
            $activeFilters['Company'] = $selectedCompany->symbol . ' - ' . $selectedCompany->name;
        }
        if (!empty($filters['exchange'])) {
            $activeFilters['Exchange'] = $filters['exchange'];
        }
        if (!empty($filters['sector'])) {
            $activeFilters['Sector'] = $filters['sector'];
        }
        if (!empty($filters['type']) && strtoupper($filters['type']) !== 'ALL') {
            $activeFilters['Transaction Type'] = strtoupper($filters['type']);
        }
    @endphp

    @if (!empty($activeFilters))
        <div class="filters-box">
            <strong>Active Filters:</strong>
            @foreach ($activeFilters as $k => $v)
                <span class="filter-chip">{{ $k }}: {{ $v }}</span>
            @endforeach
        </div>
    @endif

    <!-- 1. Portfolio Summary -->
    <div class="section-title">1. Current Portfolio Status</div>
    <table class="summary-grid" cellpadding="0" cellspacing="4">
        <tr>
            <td class="summary-card">
                <div class="summary-card-label">Inventory Cost Basis</div>
                <div class="summary-card-val">{{ number_format((float) $portfolioSummary['total_inventory_cost'], 2) }}</div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Total Quantity</div>
                <div class="summary-card-val">{{ number_format((float) $portfolioSummary['total_quantity'], 4) }}</div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Active Positions</div>
                <div class="summary-card-val">{{ $portfolioSummary['active_positions_count'] }}</div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Total Positions</div>
                <div class="summary-card-val">{{ $portfolioSummary['total_positions_count'] }}</div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Companies Held</div>
                <div class="summary-card-val">{{ $portfolioSummary['companies_held_count'] }}</div>
            </td>
            <td class="summary-card">
                <div class="summary-card-label">Total Realized Profit</div>
                @php $totProf = (float) $portfolioSummary['total_realized_profit']; @endphp
                <div class="summary-card-val {{ $totProf > 0 ? 'text-success' : ($totProf < 0 ? 'text-danger' : '') }}">
                    {{ $totProf > 0 ? '+' : '' }}{{ number_format($totProf, 2) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- 2. Transaction Activity -->
    <div class="section-title">2. Historical Transaction Activity</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">BUY Orders</th>
                <th class="text-center">SELL Orders</th>
                <th class="text-center">Total Orders</th>
                <th class="text-right">BUY Quantity</th>
                <th class="text-right">SELL Quantity</th>
                <th class="text-right">BUY Amount</th>
                <th class="text-right">SELL Amount</th>
                <th class="text-right">SELL Cost Basis</th>
                <th class="text-right">Realized Profit / (Loss)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center font-mono">{{ $transactionActivity['total_buy_transactions'] }}</td>
                <td class="text-center font-mono">{{ $transactionActivity['total_sell_transactions'] }}</td>
                <td class="text-center font-mono font-bold">{{ $transactionActivity['total_transactions'] }}</td>
                <td class="text-right font-mono">{{ number_format((float) $transactionActivity['total_buy_quantity'], 4) }}</td>
                <td class="text-right font-mono">{{ number_format((float) $transactionActivity['total_sell_quantity'], 4) }}</td>
                <td class="text-right font-mono">{{ number_format((float) $transactionActivity['total_buy_amount'], 2) }}</td>
                <td class="text-right font-mono">{{ number_format((float) $transactionActivity['total_sell_amount'], 2) }}</td>
                <td class="text-right font-mono">{{ number_format((float) $transactionActivity['total_sell_cost'], 2) }}</td>
                @php $actProf = (float) $transactionActivity['total_realized_profit']; @endphp
                <td class="text-right font-mono font-bold {{ $actProf > 0 ? 'text-success' : ($actProf < 0 ? 'text-danger' : '') }}">
                    {{ $actProf > 0 ? '+' : '' }}{{ number_format($actProf, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 3. Company-Wise Summary -->
    <div class="section-title">3. Company-Wise Portfolio Summary</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Company</th>
                <th>Symbol</th>
                <th>Exchange</th>
                <th class="text-center">BUY Tx</th>
                <th class="text-center">SELL Tx</th>
                <th class="text-right">BUY Qty</th>
                <th class="text-right">SELL Qty</th>
                <th class="text-right">BUY Amount</th>
                <th class="text-right">SELL Amount</th>
                <th class="text-right">Realized Profit</th>
                <th class="text-right">Current Qty</th>
                <th class="text-right">Cost Basis</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($companyWiseReport as $item)
                <tr>
                    <td>{{ $item['company']->name }}</td>
                    <td class="font-bold">{{ $item['company']->symbol }}</td>
                    <td>{{ $item['company']->exchange }}</td>
                    <td class="text-center font-mono">{{ $item['buy_transactions_count'] }}</td>
                    <td class="text-center font-mono">{{ $item['sell_transactions_count'] }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $item['total_buy_quantity'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $item['total_sell_quantity'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $item['total_buy_amount'], 2) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $item['total_sell_amount'], 2) }}</td>
                    @php $cProf = (float) $item['total_realized_profit']; @endphp
                    <td class="text-right font-mono font-bold {{ $cProf > 0 ? 'text-success' : ($cProf < 0 ? 'text-danger' : '') }}">
                        {{ $cProf > 0 ? '+' : '' }}{{ number_format($cProf, 2) }}
                    </td>
                    <td class="text-right font-mono font-bold">{{ number_format((float) $item['current_quantity'], 4) }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format((float) $item['current_inventory_cost'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="empty-state">No company records found matching filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. Monthly Activity -->
    <div class="section-title">4. Monthly Historical Activity</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Month</th>
                <th class="text-center">BUY Tx</th>
                <th class="text-center">SELL Tx</th>
                <th class="text-right">BUY Quantity</th>
                <th class="text-right">SELL Quantity</th>
                <th class="text-right">BUY Amount</th>
                <th class="text-right">SELL Amount</th>
                <th class="text-right">Realized Profit / (Loss)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($monthlyReport as $m)
                <tr>
                    <td class="font-bold font-mono">{{ $m['month_name'] }}</td>
                    <td class="text-center font-mono">{{ $m['buy_transactions_count'] }}</td>
                    <td class="text-center font-mono">{{ $m['sell_transactions_count'] }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $m['total_buy_quantity'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $m['total_sell_quantity'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $m['total_buy_amount'], 2) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $m['total_sell_amount'], 2) }}</td>
                    @php $mProf = (float) $m['total_realized_profit']; @endphp
                    <td class="text-right font-mono font-bold {{ $mProf > 0 ? 'text-success' : ($mProf < 0 ? 'text-danger' : '') }}">
                        {{ $mProf > 0 ? '+' : '' }}{{ number_format($mProf, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-state">No monthly transaction activity found matching filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 5. Annual Activity -->
    <div class="section-title">5. Annual Summary</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Calendar Year</th>
                <th class="text-center">BUY Tx</th>
                <th class="text-center">SELL Tx</th>
                <th class="text-right">BUY Quantity</th>
                <th class="text-right">SELL Quantity</th>
                <th class="text-right">BUY Amount</th>
                <th class="text-right">SELL Amount</th>
                <th class="text-right">Realized Profit / (Loss)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($annualReport as $yr)
                <tr>
                    <td class="font-bold font-mono">{{ $yr['year'] }}</td>
                    <td class="text-center font-mono">{{ $yr['buy_transactions_count'] }}</td>
                    <td class="text-center font-mono">{{ $yr['sell_transactions_count'] }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $yr['total_buy_quantity'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $yr['total_sell_quantity'], 4) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $yr['total_buy_amount'], 2) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $yr['total_sell_amount'], 2) }}</td>
                    @php $yProf = (float) $yr['total_realized_profit']; @endphp
                    <td class="text-right font-mono font-bold {{ $yProf > 0 ? 'text-success' : ($yProf < 0 ? 'text-danger' : '') }}">
                        {{ $yProf > 0 ? '+' : '' }}{{ number_format($yProf, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty-state">No annual transaction data available matching filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- 6. Realized Profit / Loss Analysis by Company -->
    <div class="section-title">6. Realized Profit / Loss Analysis by Company</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Company</th>
                <th>Symbol</th>
                <th>Currency</th>
                <th class="text-center">Closed Orders</th>
                <th class="text-right">Total SELL Revenue</th>
                <th class="text-right">Total SELL Cost Basis</th>
                <th class="text-right">Realized Profit / (Loss)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($realizedProfitAnalysis as $rp)
                @php $rProf = (float) $rp['realized_profit']; @endphp
                <tr>
                    <td>{{ $rp['company']->name }}</td>
                    <td class="font-bold">{{ $rp['company']->symbol }}</td>
                    <td>{{ $rp['company']->currency }}</td>
                    <td class="text-center font-mono">{{ $rp['sell_transactions_count'] }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $rp['total_sell_revenue'], 2) }}</td>
                    <td class="text-right font-mono">{{ number_format((float) $rp['total_sell_cost_basis'], 2) }}</td>
                    <td class="text-right font-mono font-bold {{ $rProf > 0 ? 'text-success' : ($rProf < 0 ? 'text-danger' : '') }}">
                        {{ $rProf > 0 ? '+' : '' }}{{ number_format($rProf, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state">No closed SELL transactions / realized profit records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        Confidential Financial Accounting Report &bull; Computed using Weighted Average Cost (WAC) methodology from immutable transaction records. &bull; No market-price estimates or unrealized valuation applied.
    </div>
</body>
</html>
