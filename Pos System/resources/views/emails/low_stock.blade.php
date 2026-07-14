<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Low Stock Alert</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #991b1b; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; font-weight: bold; color: #4b5563; text-transform: uppercase; font-size: 12px; }
        td { font-size: 14px; }
        .qty-badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-weight: bold; font-size: 12px; }
        .qty-zero { background-color: #fee2e2; color: #991b1b; }
        .qty-low { background-color: #fef3c7; color: #92400e; }
        .footer { margin-top: 30px; font-size: 12px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>⚠️ Low Stock Alert</h2>
            <p>The following products have fallen below their minimum reorder level and require your attention.</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Current Stock</th>
                    <th>Reorder Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $product)
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        @if($product->barcode)
                            <br><span style="font-size:12px; color:#6b7280;">Barcode: {{ $product->barcode }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="qty-badge {{ $product->total_stock <= 0 ? 'qty-zero' : 'qty-low' }}">
                            {{ $product->total_stock }}
                        </span>
                    </td>
                    <td>{{ $product->reorder_level }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            <p>This is an automated message from {{ config('app.name', 'POS SYSTEM') }}.</p>
            <p><a href="{{ url('/') }}" style="color: #4f46e5;">Log in to the Dashboard</a></p>
        </div>
    </div>
</body>
</html>
