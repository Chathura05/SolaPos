<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashier Performance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        h1 { margin: 0 0 5px 0; font-size: 18px; }
        p { margin: 0; color: #666; }
        .summary-box { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .summary-box td { border: 1px solid #ddd; padding: 10px; text-align: center; background-color: #f8f9fa; }
        .summary-title { font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 5px; }
        .summary-value { font-size: 16px; font-weight: bold; color: #333; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        table.data-table th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ setting('store_name', config('app.name')) }}</h1>
        <p>Showroom: {{ $showroomName }}</p>
        <p>Cashier Performance Report - {{ now()->format('d M Y, h:i A') }}</p>
        <p>Period: {{ ucfirst(str_replace('_', ' ', $period)) }} 
           @if($period === 'custom') ({{ $dateFrom }} to {{ $dateTo }}) @endif
        </p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Revenue</div>
                <div class="summary-value">Rs. {{ number_format($overallTotal, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Total Sales</div>
                <div class="summary-value">{{ $overallCount }}</div>
            </td>
            <td>
                <div class="summary-title">Active Cashiers</div>
                <div class="summary-value">{{ $cashierStats->count() }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">Rank</th>
                <th>Cashier Name</th>
                <th class="text-right">Sales Count</th>
                <th class="text-right">Total Revenue (Rs.)</th>
                <th class="text-right">Avg Sale (Rs.)</th>
                <th class="text-right">Best Sale (Rs.)</th>
                <th class="text-right">Discounts (Rs.)</th>
                <th class="text-right">Contribution</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashierStats as $index => $stat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stat->cashier_name }}</td>
                <td class="text-right">{{ $stat->total_sales }}</td>
                <td class="text-right"><strong>{{ number_format($stat->total_revenue, 2) }}</strong></td>
                <td class="text-right">{{ number_format($stat->avg_sale, 2) }}</td>
                <td class="text-right">{{ number_format($stat->max_sale, 2) }}</td>
                <td class="text-right">{{ number_format($stat->total_discount, 2) }}</td>
                <td class="text-right">
                    {{ $overallTotal > 0 ? round(($stat->total_revenue / $overallTotal) * 100, 1) : 0 }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No cashier activity found for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
        @if($cashierStats->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="2"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ $overallCount }}</strong></td>
                <td class="text-right"><strong>{{ number_format($overallTotal, 2) }}</strong></td>
                <td colspan="4"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
