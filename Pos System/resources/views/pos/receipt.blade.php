<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            @if(isset($isPdf) && $isPdf)
            background: white;
            padding: 20px;
            @else
            background: #6b7280;
            display: flex;
            justify-content: center;
            padding: 20px;
            @endif
        }

        .receipt-wrapper {
            @if(isset($isPdf) && $isPdf)
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            @else
            width: 302px; /* ~80mm at 96dpi */
            @endif
        }

        .receipt {
            background: white;
            width: 100%;
            @if(isset($isPdf) && $isPdf)
            padding: 0;
            box-shadow: none;
            @else
            padding: 8px 10px;
            border-radius: 4px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.2);
            @endif
            font-size: 13px; /* Increased from 11px */
            line-height: 1.4;
            color: #000; /* Darker black for thermal printing */
        }

        /* ── Header ── */
        .header {
            text-align: center;
            border-bottom: 1px dashed #999;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .shop-name {
            font-size: 18px; /* Increased */
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .shop-sub {
            font-size: 12px; /* Increased */
            color: #000;
            margin-top: 1px;
        }
        .invoice-no {
            font-size: 14px; /* Increased */
            font-weight: bold;
            margin-top: 4px;
        }
        .receipt-meta {
            font-size: 12px; /* Increased */
            color: #000;
            margin-top: 2px;
        }

        /* ── Items Table ── */
        .items-section {
            margin: 6px 0;
        }
        .items-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            display: none; /* Hide 'ITEMS' title to match design */
        }
        .items-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }
        .items-table thead tr {
            border-bottom: 1px dashed #000;
        }
        .items-table th {
            padding: 4px 0;
            font-weight: bold;
            font-size: 11px;
            color: #000;
        }
        .items-table th:nth-child(1) { text-align: left; padding-left: 4px; width: 20%; }
        .items-table th:nth-child(2) { text-align: center; width: 35%; }
        .items-table th:nth-child(3) { text-align: right; width: 15%; }
        .items-table th:nth-child(4) { text-align: right; padding-right: 4px; width: 30%; }

        .items-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 12px;
            color: #000;
        }
        .items-table td:nth-child(2) { text-align: center; }
        .items-table td:nth-child(3) { text-align: right; white-space: nowrap; }
        .items-table td:nth-child(4) { text-align: right; white-space: nowrap; }

        /* ── Dividers ── */
        .divider {
            border: none;
            border-top: 1px dashed #999;
            margin: 4px 0;
        }

        /* ── Totals ── */
        .totals { font-size: 13px; font-weight: bold; color: #000; }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }
        .total-row.main {
            font-size: 16px; /* Huge for total */
            font-weight: 900;
            padding-top: 4px;
            border-top: 2px solid #000;
            margin-top: 3px;
        }

        /* ── Payment ── */
        .payment-info {
            font-size: 13px;
            font-weight: bold;
            color: #000;
            margin-top: 6px;
            border: 1px dashed #000;
            border-radius: 2px;
            padding: 6px;
        }
        .payment-info .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .payment-info .change { font-weight: 900; font-size: 14px; }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 8px;
        }
        .footer .thank {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
            color: #000;
        }
        .footer p {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            line-height: 1.3;
        }

        /* ── Thermal Printer: RONGTA RP350 80mm ── */
        @media print {
            @page {
                margin: 0;
                size: 80mm auto;
            }
            html, body {
                width: 100%;
                margin: 0;
                padding: 0;
                background: white;
            }
            .receipt-wrapper {
                width: 72mm; /* Standard printable area for 80mm paper */
                max-width: 100%;
                margin: 0 auto; /* Center on the page */
            }
            .receipt {
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                border-radius: 0;
                padding: 2mm 0; /* Remove side padding so it can use the full 72mm */
                margin: 0 auto;
            }
            /* Prevent rows from splitting in half across page breaks */
            tr {
                page-break-inside: avoid;
                break-inside: avoid;
            }
            /* Stop Chrome from repeating the table header on every new page */
            thead {
                display: table-row-group;
            }
            .no-print { display: none !important; }
        }

        /* ── Screen Buttons ── */
        .print-btn {
            display: block;
            margin: 12px auto 0;
            padding: 10px 24px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }
        .print-btn:hover { background: #4f46e5; }
        .back-btn {
            display: block;
            margin: 8px auto 0;
            padding: 8px 24px;
            background: transparent;
            color: #e2e8f0;
            border: 1px solid #6b7280;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            font-family: inherit;
            text-decoration: none;
            text-align: center;
        }
        .back-btn:hover { border-color: #e2e8f0; color: white; }
    </style>
</head>
<body>

<div class="receipt-wrapper">
    <div class="receipt">
        {{-- Header --}}
        <div class="header">
            @php
                $logo = setting('company_logo');
                $logoSrc = '';
                if ($logo) {
                    $path = storage_path('app/public/' . $logo);
                    if (file_exists($path)) {
                        $type = pathinfo($path, PATHINFO_EXTENSION);
                        $data = file_get_contents($path);
                        $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    } else {
                        $path = public_path('storage/' . $logo);
                        if (file_exists($path)) {
                            $type = pathinfo($path, PATHINFO_EXTENSION);
                            $data = file_get_contents($path);
                            $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                    }
                }
            @endphp
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Company Logo" style="max-width: 100%; height: auto; max-height: 60px; margin: 0 auto 5px; display: block; object-fit: contain;">
            @endif
            <div class="shop-name">SOLA CHEMICAL COMPANY (PVT) Ltd.</div>
            @if(setting('store_address'))<div class="shop-sub">{{ setting('store_address') }}</div>@endif
            @if(setting('store_phone'))<div class="shop-sub">{{ setting('store_phone') }}</div>@endif
            <div class="invoice-no">{{ $sale->invoice_number }}</div>
            <div class="receipt-meta">
                {{ $sale->created_at->format('d M Y, h:i A') }}
            </div>
            <div class="receipt-meta">Cashier: {{ $sale->cashier->name ?? '-' }}</div>
            @if($sale->customer_name)
            <div class="receipt-meta">Customer: {{ $sale->customer_name }} {{ $sale->customer_phone ? '| ' . $sale->customer_phone : '' }}</div>
            @endif
        </div>

        {{-- Items --}}
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Disc</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $item)
                    <tr>
                        <td colspan="4" style="font-weight: bold; padding-top: 6px; font-size: 13px;">
                            {{ sprintf('%02d', $loop->iteration) }} &nbsp; {{ $item->product_name }}
                        </td>
                    </tr>
                    <tr style="border-bottom: 1px dashed #ccc;">
                        <td style="padding-bottom: 6px; padding-left: 4px;">{{ $item->quantity }} PCS</td>
                        <td style="padding-bottom: 6px; text-align: center;">x {{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding-bottom: 6px; text-align: right;">0.00</td>
                        <td style="padding-bottom: 6px; text-align: right; padding-right: 4px; font-weight: bold;">{{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <hr class="divider">

        {{-- Totals --}}
        <div class="totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->subtotal, 2) }}</span>
            </div>
            @if($sale->discount_amount > 0)
            <div class="total-row">
                <span>Discount ({{ $sale->discount_type === 'percent' ? $sale->discount_value . '%' : 'Fixed' }})</span>
                <span>-{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->discount_amount, 2) }}</span>
            </div>
            @endif
            @if($sale->tax_amount > 0)
            <div class="total-row">
                <span>Tax ({{ $sale->tax_percent }}%)</span>
                <span>+{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row main">
                <span>TOTAL</span>
                <span>{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        {{-- Payment --}}
        <div class="payment-info">
            <div class="row">
                <span>Payment:</span>
                <span>{{ strtoupper($sale->payment_method) }}</span>
            </div>
            <div class="row">
                <span>Paid:</span>
                <span>{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            <div class="row">
                <span>Change:</span>
                <span class="change">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($sale->change_amount, 2) }}</span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div class="thank">Thank You!</div>
            <p>{!! nl2br(e(setting('receipt_footer', 'Thank you for your purchase!'))) !!}</p>
        </div>

        @if(!isset($isPdf) || !$isPdf)
        {{-- Buttons (hidden on print) --}}
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">🖨 Print Receipt</button>
            <a href="{{ route('pos.index') }}" class="back-btn">← Back to POS</a>
        </div>
        @endif
    </div>
</div>

@if(!isset($isPdf) || !$isPdf)
<script>
    // Auto-print when loaded
    window.onload = function() {
        window.print();
        // Close the window after printing (or if cancelled)
        setTimeout(function() {
            window.close();
        }, 500);
    };
</script>
@endif
</body>
</html>
