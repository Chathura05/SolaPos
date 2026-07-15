<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
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
        <p>Sales Report - {{ now()->format('d M Y, h:i A') }}</p>
        <p>Period: {{ ucfirst(str_replace('_', ' ', $period)) }} 
           @if($period === 'custom') ({{ $dateFrom }} to {{ $dateTo }}) @endif
        </p>
    </div>

    <table class="summary-box">
        <tr>
            <td>
                <div class="summary-title">Total Revenue</div>
                <div class="summary-value">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($totalRevenue, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Transactions</div>
                <div class="summary-value">{{ $totalTransactions }}</div>
            </td>
            <td>
                <div class="summary-title">Avg Order</div>
                <div class="summary-value">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($avgOrderValue, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Discounts</div>
                <div class="summary-value">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($totalDiscount, 2) }}</div>
            </td>
            <td>
                <div class="summary-title">Tax Collected</div>
                <div class="summary-value">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($totalTax, 2) }}</div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Date & Time</th>
                <th>Cashier</th>
                <th>Customer</th>
                <th>Payment Method</th>
                <th class="text-right">Subtotal ({{ setting('currency_symbol', 'Rs.') }})</th>
                <th class="text-right">Discount ({{ setting('currency_symbol', 'Rs.') }})</th>
                <th class="text-right">Total ({{ setting('currency_symbol', 'Rs.') }})</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_number }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->cashier->name ?? '—' }}</td>
                <td>{{ $sale->customer_name ?: 'Walk-in' }}</td>
                <td>{{ ucfirst($sale->payment_method) }}</td>
                <td class="text-right">{{ number_format($sale->subtotal, 2) }}</td>
                <td class="text-right">{{ number_format($sale->discount_amount, 2) }}</td>
                <td class="text-right"><strong>{{ number_format($sale->total_amount, 2) }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No sales found for the selected period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
