<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory History Report</title>
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
    <h2>Inventory History Report</h2>
    <p>Generated on: {{ date('Y-m-d H:i:s') }}</p>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Product</th>
                <th>Showroom</th>
                <th>Type</th>
                <th class="text-right">Qty Changed</th>
                <th class="text-right">After Qty</th>
                <th>User</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $movement->product->name ?? 'Unknown' }}</td>
                    <td>{{ $movement->showroom->name ?? 'Unknown' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $movement->type)) }}</td>
                    <td class="text-right">
                        @if($movement->quantity > 0)
                            +{{ $movement->quantity }}
                        @else
                            {{ $movement->quantity }}
                        @endif
                    </td>
                    <td class="text-right">{{ $movement->after_quantity }}</td>
                    <td>{{ $movement->user->name ?? 'System' }}</td>
                    <td>{{ $movement->reference }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
