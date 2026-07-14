<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $sale->invoice_number }}</title>
    <style>
        /* ─── Reset ─── */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f0f4f8; }

        /* ─── Typography & Colors ─── */
        body, p, td, span, a, h1, h2, h3, h4, h5, h6 { font-family: 'Segoe UI', Arial, Helvetica, sans-serif; color: #1a202c; }
        
        .wrapper { width: 100%; max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; }
        
        /* ─── Header ─── */
        .header { background-image: linear-gradient(135deg, #4f46e5 0%, #6366f1 60%, #818cf8 100%); background-color: #4f46e5; padding: 32px 36px 28px; text-align: center; }
        .header-title { color: #ffffff; font-size: 22px; font-weight: 800; letter-spacing: 0.5px; margin: 0 0 4px 0; }
        .header-meta { color: #e0e7ff; font-size: 13px; line-height: 1.6; margin: 0; }
        .header-badge { display: inline-block; background-color: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35); border-radius: 20px; padding: 5px 16px; color: #ffffff; font-size: 13px; font-weight: 600; margin-top: 16px; }

        /* ─── Info Section ─── */
        .info-cell { padding: 20px 36px 16px 36px; border-bottom: 1px solid #e8edf2; }
        .info-label { font-size: 12px; color: #6b7280; margin: 0 0 2px 0; text-transform: uppercase; }
        .info-val { font-size: 15px; color: #111827; font-weight: 700; margin: 0; }
        
        .status-pill { background-color: #d1fae5; color: #065f46; font-size: 11px; font-weight: 700; padding: 4px 12px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block; margin-top: 4px; }

        /* ─── Meta (Cashier/Showroom) ─── */
        .meta-container { background-color: #f8fafc; padding: 16px 36px; border-bottom: 1px solid #e8edf2; }
        .meta-label { font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 3px 0; }
        .meta-val { font-size: 14px; font-weight: 600; color: #374151; margin: 0; }

        /* ─── Items Section ─── */
        .items-container { padding: 16px 36px 10px 36px; }
        .items-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; padding-bottom: 10px; border-bottom: 2px solid #e8edf2; margin: 0 0 12px 0; }
        
        .item-row { border-bottom: 1px solid #f3f4f6; }
        .item-cell { padding: 12px 0; vertical-align: top; }
        
        .item-num { display: inline-block; width: 22px; height: 22px; background-color: #ede9fe; color: #6d28d9; border-radius: 50%; font-size: 11px; font-weight: 700; line-height: 22px; text-align: center; }
        .item-name { font-size: 14px; font-weight: 600; color: #111827; margin: 0 0 2px 0; }
        .item-meta { font-size: 12px; color: #6b7280; margin: 0; }
        .item-total { font-size: 14px; font-weight: 700; color: #1e293b; text-align: right; }

        /* ─── Totals Section ─── */
        .totals-container { background-color: #f8fafc; margin: 0 36px; border-radius: 10px; padding: 14px 18px; }
        .total-row td { padding: 4px 0; font-size: 13px; color: #6b7280; }
        .total-row td.val { text-align: right; }
        
        .total-main td { font-size: 18px; font-weight: 800; color: #111827; padding-top: 10px; margin-top: 6px; border-top: 2px solid #e8edf2; }
        .total-main td.val { color: #4f46e5; text-align: right; }
        
        .txt-green { color: #16a34a; }
        .txt-orange { color: #d97706; }

        /* ─── Payment Info ─── */
        .payment-container { padding: 16px 36px 0 36px; }
        .badge { display: inline-block; padding: 6px 14px; border-radius: 8px; font-size: 13px; font-weight: 600; margin-right: 8px; margin-bottom: 8px; }
        .badge.cash { background-color: #d1fae5; color: #065f46; }
        .badge.card { background-color: #dbeafe; color: #1e3a8a; }
        .badge.other { background-color: #ede9fe; color: #4c1d95; }
        .badge.change { background-color: #fef3c7; color: #92400e; }

        /* ─── Footer ─── */
        .footer { text-align: center; padding: 28px 36px 32px 36px; border-top: 1px solid #e8edf2; margin-top: 16px; }
        .footer-title { font-size: 20px; font-weight: 800; color: #4f46e5; margin: 0 0 8px 0; }
        .footer-text { font-size: 13px; color: #6b7280; line-height: 1.6; margin: 0 0 20px 0; }
        .footer-powered { font-size: 11px; color: #9ca3af; letter-spacing: 0.3px; margin: 0; }

        @media screen and (max-width: 600px) {
            .info-cell, .meta-container, .items-container, .payment-container, .footer { padding-left: 20px !important; padding-right: 20px !important; }
            .totals-container { margin-left: 20px !important; margin-right: 20px !important; }
            .meta-block { display: block !important; width: 100% !important; margin-bottom: 12px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 24px 8px; background-color: #f0f4f8;">
    
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td align="center">
                <!-- Wrapper Table -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapper" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.10);">
                    
                    <!-- Header -->
                    <tr>
                        <td class="header" align="center">
                            <p class="header-title">{{ strtoupper($storeName) }}</p>
                            @if($storeAddress || $storePhone)
                                <p class="header-meta">
                                    @if($storeAddress){{ $storeAddress }}@endif
                                    @if($storeAddress && $storePhone) &nbsp;&middot;&nbsp; @endif
                                    @if($storePhone){{ $storePhone }}@endif
                                </p>
                            @endif
                            <span class="header-badge">&#128231; E-Bill / Digital Receipt</span>
                        </td>
                    </tr>

                    <!-- Invoice Info -->
                    <tr>
                        <td class="info-cell">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td width="50%" align="left" valign="top">
                                        <p class="info-label">Invoice Number</p>
                                        <p class="info-val">{{ $sale->invoice_number }}</p>
                                    </td>
                                    <td width="50%" align="right" valign="top">
                                        <p class="info-label">Date &amp; Time</p>
                                        <p class="info-val" style="margin-bottom: 4px;">{{ $sale->created_at->format('d M Y, h:i A') }}</p>
                                        <span class="status-pill">&#10003; {{ ucfirst($sale->status) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Cashier & Customer -->
                    <tr>
                        <td class="meta-container">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="meta-block" valign="top" width="33%">
                                        <p class="meta-label">Served By</p>
                                        <p class="meta-val">{{ $sale->cashier?->name ?? '—' }}</p>
                                    </td>
                                    @if($sale->showroom)
                                    <td class="meta-block" valign="top" width="33%">
                                        <p class="meta-label">Showroom</p>
                                        <p class="meta-val">{{ $sale->showroom->name }}</p>
                                    </td>
                                    @endif
                                    @if($sale->customer_name)
                                    <td class="meta-block" valign="top" width="34%">
                                        <p class="meta-label">Customer</p>
                                        <p class="meta-val">{{ $sale->customer_name }}</p>
                                    </td>
                                    @endif
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Items List -->
                    <tr>
                        <td class="items-container">
                            <p class="items-title">Items Purchased</p>
                            
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                @foreach($sale->items as $item)
                                <tr>
                                    <td class="item-cell" width="30" align="left">
                                        <span class="item-num">{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="item-cell" align="left">
                                        <p class="item-name">{{ $item->product_name }}</p>
                                        <p class="item-meta">{{ $item->quantity }} &times; {{ $currency }} {{ number_format($item->unit_price, 2) }}</p>
                                    </td>
                                    <td class="item-cell item-total" width="100" align="right" valign="bottom">
                                        {{ $currency }} {{ number_format($item->subtotal, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <!-- Totals -->
                    <tr>
                        <td style="padding: 12px 36px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="totals-container">
                                <tr>
                                    <td style="padding: 14px 18px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr class="total-row">
                                                <td align="left">Subtotal</td>
                                                <td align="right" class="val">{{ $currency }} {{ number_format($sale->subtotal, 2) }}</td>
                                            </tr>
                                            @if($sale->discount_amount > 0)
                                            <tr class="total-row">
                                                <td align="left">Discount @if($sale->discount_type === 'percent')({{ $sale->discount_value }}%)@endif</td>
                                                <td align="right" class="val txt-orange">&minus; {{ $currency }} {{ number_format($sale->discount_amount, 2) }}</td>
                                            </tr>
                                            @endif
                                            @if($sale->tax_amount > 0)
                                            <tr class="total-row">
                                                <td align="left">Tax ({{ $sale->tax_percent }}%)</td>
                                                <td align="right" class="val txt-green">+ {{ $currency }} {{ number_format($sale->tax_amount, 2) }}</td>
                                            </tr>
                                            @endif
                                            <tr class="total-main">
                                                <td align="left">Total</td>
                                                <td align="right" class="val">{{ $currency }} {{ number_format($sale->total_amount, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Payment Info -->
                    <tr>
                        <td class="payment-container">
                            <span class="badge {{ $sale->payment_method }}">
                                {{ $sale->payment_method === 'cash' ? '💵' : ($sale->payment_method === 'card' ? '💳' : '📱') }}
                                Paid via {{ ucfirst($sale->payment_method) }}
                            </span>
                            @if($sale->change_amount > 0)
                            <span class="badge change">&#128260; Change: {{ $currency }} {{ number_format($sale->change_amount, 2) }}</span>
                            @endif
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer">
                            <p class="footer-title">&#127881; Thank You!</p>
                            <p class="footer-text">{!! nl2br(e($footerText)) !!}</p>
                            <p class="footer-powered">This e-bill was sent automatically from {{ $storeName }}</p>
                        </td>
                    </tr>

                </table>
                <!-- End Wrapper -->
            </td>
        </tr>
    </table>

</body>
</html>
