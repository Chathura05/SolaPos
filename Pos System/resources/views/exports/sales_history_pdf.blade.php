<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales History Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f4f4f4; }
        h2 { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <h2>Sales History Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice #</th>
                <th>Customer</th>
                <th>Cashier</th>
                <th>Showroom</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->customer_name ?? 'Walk-in' }}</td>
                    <td>{{ $sale->cashier->name ?? 'Unknown' }}</td>
                    <td>{{ $sale->showroom->name ?? 'Unknown' }}</td>
                    <td>{{ ucfirst($sale->status) }}</td>
                    <td class="text-right">{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
