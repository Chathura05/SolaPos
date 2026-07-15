<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quote {{ $quote->quote_number }}</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #ddd; padding-bottom: 20px; margin-bottom: 30px; }
        .header h2 { margin: 0 0 10px 0; color: #4f46e5; }
        .header p { margin: 0; color: #666; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; width: 48%; }
        .info-box h4 { margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 12px 8px; text-align: left; }
        th { background: #f9fafb; font-weight: 600; color: #374151; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 1.1em; background: #f9fafb; }
        .total-row td { border-top: 2px solid #ddd; border-bottom: none; }
        .print-btn { display: block; width: 150px; margin: 40px auto; padding: 12px; text-align: center; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .print-btn:hover { background: #4338ca; }
        .notes { margin-top: 30px; padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>QUOTATION / ESTIMATE</h2>
        <p>Quote #: <strong>{{ $quote->quote_number }}</strong> | Date: <strong>{{ $quote->created_at->format('d M Y') }}</strong></p>
    </div>
    
    <div class="info-section">
        <div class="info-box">
            <h4>Bill To</h4>
            <strong>{{ $quote->customer_name ?? 'Walk-in Customer' }}</strong><br>
            @if($quote->customer_phone) Phone: {{ $quote->customer_phone }}<br> @endif
        </div>
        <div class="info-box">
            <h4>From</h4>
            <strong>{{ $quote->showroom->name ?? 'Head Office' }}</strong><br>
            @if($quote->showroom && $quote->showroom->address) {{ $quote->showroom->address }}<br> @endif
            @if($quote->showroom && $quote->showroom->phone) Phone: {{ $quote->showroom->phone }}<br> @endif
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right" style="padding-top:20px;">Subtotal</td>
                <td class="text-right" style="padding-top:20px;">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($quote->subtotal, 2) }}</td>
            </tr>
            @if($quote->discount_amount > 0)
            <tr>
                <td colspan="3" class="text-right">Discount</td>
                <td class="text-right">-{{ setting('currency_symbol', 'Rs.') }} {{ number_format($quote->discount_amount, 2) }}</td>
            </tr>
            @endif
            @if($quote->tax_amount > 0)
            <tr>
                <td colspan="3" class="text-right">Tax ({{ $quote->tax_percent }}%)</td>
                <td class="text-right">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($quote->tax_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="3" class="text-right">Total</td>
                <td class="text-right">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($quote->total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    
    @if($quote->notes)
    <div class="notes">
        <strong>Notes:</strong><br>
        {{ $quote->notes }}
    </div>
    @endif
    
    <a href="javascript:window.print()" class="print-btn">Print Quote</a>
</body>
</html>
