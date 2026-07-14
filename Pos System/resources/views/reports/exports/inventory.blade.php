<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        h1 { margin: 0 0 5px 0; font-size: 18px; }
        p { margin: 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .out-of-stock { color: #dc2626; }
        .low-stock { color: #d97706; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ setting('store_name', config('app.name')) }}</h1>
        <p>Showroom: {{ $showroomName }}</p>
        <p>Inventory Report - {{ now()->format('d M Y, h:i A') }}</p>
        <p>Filter: {{ ucfirst(str_replace('_', ' ', $filter)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Barcode</th>
                <th>Category</th>
                <th class="text-right">Stock Qty</th>
                <th class="text-right">Reorder Level</th>
                <th class="text-right">Unit Cost (Rs.)</th>
                <th class="text-right">Stock Value (Rs.)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
            @php
                $isOutOfStock = $product->total_stock <= 0;
                $isLowStock = !$isOutOfStock && $product->total_stock <= $product->reorder_level;
            @endphp
            <tr>
                <td>{{ $product->name }}</td>
                <td>{{ $product->barcode }}</td>
                <td>{{ $product->category->name ?? '—' }}</td>
                <td class="text-right">
                    {{ $product->total_stock }} {{ $product->unit }}
                </td>
                <td class="text-right">{{ $product->reorder_level }}</td>
                <td class="text-right">{{ number_format($product->cost_price, 2) }}</td>
                <td class="text-right">{{ number_format($product->total_stock * $product->cost_price, 2) }}</td>
                <td class="text-center">
                    @if($isOutOfStock)
                        <span class="out-of-stock">Out of Stock</span>
                    @elseif($isLowStock)
                        <span class="low-stock">Low Stock</span>
                    @else
                        <span>In Stock</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
