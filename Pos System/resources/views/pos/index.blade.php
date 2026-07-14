<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ setting('store_name', config('app.name')) }} - POS Terminal</title>
    @if(setting('company_logo'))
        <link rel="icon" href="{{ Storage::url(setting('company_logo')) }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; height: 100vh; display: flex; flex-direction: column; overflow: hidden; }

        /* Top Bar */
        .topbar { background: #ffffff; border-bottom: 3px solid #f97316; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); z-index: 10;}
        .topbar .logo { font-size: 1.1rem; font-weight: 800; color: #f97316; letter-spacing: -0.5px; display: flex; align-items: center; gap: 10px; }
        .topbar .info { display: flex; gap: 20px; align-items: center; font-size: 0.8rem; color: #64748b; }
        .topbar .info span b { color: #1e293b; font-weight: 600; }
        .topbar .nav-links { display: flex; gap: 8px; }
        .topbar .nav-links a, .topbar .nav-links button { color: #64748b; font-size: 0.8rem; font-weight: 500; text-decoration: none; padding: 6px 12px; border-radius: 6px; transition: all .2s; background: transparent; border: none; cursor: pointer; font-family: inherit; }
        .topbar .nav-links a:hover, .topbar .nav-links button:hover { background: #fff7ed; color: #ea580c; }
        .topbar .nav-links a.logout { color: #ef4444; }
        .topbar .nav-links a.logout:hover { background: #fef2f2; color: #dc2626; }

        /* Main layout */
        .pos-layout { display: grid; grid-template-columns: 1fr 450px 420px; height: calc(100vh - 53px); overflow: hidden; }

        /* LEFT: Product Panel */
        .product-panel { display: flex; flex-direction: column; background: #f1f5f9; border-right: 1px solid #e2e8f0; overflow: hidden; }

        .search-section { padding: 14px 16px; background: #ffffff; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
        .search-wrap { position: relative; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1rem; }
        #searchInput { width: 100%; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 10px 12px 10px 38px; color: #1e293b; font-size: 0.9rem; outline: none; transition: all .2s; font-family: 'Inter', sans-serif; }
        #searchInput:focus { border-color: #f97316; background: #ffffff; box-shadow: 0 0 0 3px rgba(249,115,22,0.15); }
        #searchInput::placeholder { color: #94a3b8; }

        .search-hint { font-size: 0.72rem; color: #64748b; margin-top: 6px; padding-left: 2px; }

        /* Product results */
        .product-results { flex: 1; overflow-y: auto; padding: 16px; }
        .product-results::-webkit-scrollbar { width: 14px; }
        .product-results::-webkit-scrollbar-track { background: transparent; }
        .product-results::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }

        .product-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 12px; cursor: pointer; transition: all .2s; user-select: none; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .product-card:hover { border-color: #fbbf24; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.1); }
        .product-card:active { transform: scale(0.97); }
        .product-card.out-of-stock { opacity: 0.5; pointer-events: none; background: #f8fafc; }
        .product-card .icon { font-size: 1.8rem; margin-bottom: 8px; }
        .product-card .pname { font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 3px; line-height: 1.3; }
        .product-card .psku { font-size: 0.7rem; color: #94a3b8; font-family: monospace; }
        .product-card .pprice { font-size: 1.05rem; font-weight: 700; color: #c2410c; margin-top: 6px; }
        .product-card .pstock { font-size: 0.7rem; color: #64748b; margin-top: 4px; font-weight: 500; }
        .product-card .pstock.low { color: #dc2626; font-weight: 700; }

        .no-results { text-align: center; padding: 60px 20px; color: #64748b; }
        .no-results .icon { font-size: 3rem; margin-bottom: 10px; }

        /* MIDDLE: Cart Panel */
        .cart-panel { display: flex; flex-direction: column; background: #ffffff; overflow: hidden; border-right: 1px solid #e2e8f0; }

        /* RIGHT: Payment Panel */
        .payment-panel { display: flex; flex-direction: column; background: #f8fafc; overflow: hidden; }

        .cart-header { padding: 10px 16px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; background: #ffffff; }
        .cart-header h3 { font-size: 0.95rem; font-weight: 700; color: #1e293b; letter-spacing: 0.5px; text-transform: uppercase; display: flex; align-items: center; gap: 6px;}
        .cart-header .invoice-badge { font-size: 0.75rem; color: #d97706; font-family: monospace; margin-top: 4px; font-weight: 600; }

        .cart-customer { padding: 8px 14px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; background: #f8fafc; }
        .cart-customer .cust-row { display: flex; gap: 8px; }
        .cart-customer .email-row { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
        .cart-customer .email-row input[type="email"] { flex: 1; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 14px; color: #1e293b; font-size: 0.95rem; outline: none; font-family: 'Inter', sans-serif; }
        .cart-customer .email-row input[type="email"]:focus { border-color: #fbbf24; }
        .cart-customer .email-row input[type="email"]::placeholder { color: #94a3b8; }
        .cart-customer .email-row .ebill-label { font-size: 0.75rem; color: #475569; white-space: nowrap; display: flex; align-items: center; gap: 5px; cursor: pointer; font-weight: 500; }
        .cart-customer .email-row .ebill-label input[type="checkbox"] { accent-color: #f97316; width: 14px; height: 14px; cursor: pointer; }
        .cart-customer input { flex: 1; min-width: 0; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 14px; color: #1e293b; font-size: 0.95rem; outline: none; font-family: 'Inter', sans-serif; }
        .cart-customer input:focus { border-color: #fbbf24; }
        .cart-customer input::placeholder { color: #94a3b8; }

        /* Cart Items */
        .cart-items { flex: 1; overflow-y: auto; padding: 8px 14px; min-height: 0; }
        .cart-items::-webkit-scrollbar { width: 14px; }
        .cart-items::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

        .cart-empty { text-align: center; padding: 20px; color: #94a3b8; font-size: 0.9rem; }
        .cart-empty .icon { font-size: 2.5rem; display: block; margin-bottom: 8px; color: #cbd5e1; }

        .cart-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px dashed #e2e8f0; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item .item-info { flex: 1; min-width: 0; }
        .cart-item .item-name { font-size: 0.85rem; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cart-item .item-price { font-size: 0.75rem; color: #64748b; margin-top: 3px; }
        .cart-item .qty-ctrl { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
        .qty-btn { width: 44px; height: 44px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; color: #475569; font-size: 1.4rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .2s; font-weight: 600; }
        .qty-btn:hover { background: #fff7ed; color: #ea580c; border-color: #fbbf24; }
        .qty-val { font-size: 1.1rem; font-weight: 700; min-width: 32px; text-align: center; color: #1e293b; }
        .cart-item .item-total { font-size: 1.1rem; font-weight: 700; color: #c2410c; min-width: 72px; text-align: right; flex-shrink: 0; }
        .remove-btn { background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; cursor: pointer; font-size: 1.2rem; padding: 8px 12px; border-radius: 8px; transition: all .2s; display: flex; align-items: center; justify-content: center; }
        .remove-btn:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }

        /* Totals */
        .cart-totals { padding: 16px 20px; border-bottom: 1px solid #e2e8f0; flex-shrink: 0; background: #ffffff; }
        .total-row { display: flex; justify-content: space-between; align-items: center; font-size: 0.95rem; color: #64748b; margin-bottom: 6px; }
        .total-row.main { font-size: 1.2rem; font-weight: 700; color: #1e293b; padding-top: 10px; border-top: 2px dashed #cbd5e1; margin-top: 10px; }
        .total-row .val { color: #334155; font-weight: 600; font-size: 1.05rem;}
        .total-row.main .val { color: #16a34a; font-size: 1.6rem; font-weight: 800; letter-spacing: -0.5px; }

        /* Discount & Tax */
        .discount-row { display: flex; gap: 8px; margin-bottom: 12px; align-items: center; }
        .discount-row select, .discount-row input { background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px; color: #1e293b; font-size: 0.95rem; outline: none; font-family: 'Inter', sans-serif; min-width: 0; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02); transition: all .2s; cursor: pointer; }
        .discount-row select:focus, .discount-row input:focus { border-color: #fbbf24; }
        .discount-row select { flex-shrink: 0; }
        .discount-row input { flex: 1; }
        .discount-row label { font-size: 0.8rem; color: #475569; flex-shrink: 0; font-weight: 600; white-space: nowrap; }

        /* Payment section */
        .payment-section { padding: 20px; flex: 1; display: flex; flex-direction: column; background: #f8fafc; }

        .payment-methods { display: flex; gap: 10px; margin-bottom: 20px; }
        .pay-method { flex: 1; padding: 14px 6px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; color: #64748b; font-size: 0.95rem; font-weight: 700; cursor: pointer; text-align: center; transition: all .2s; font-family: 'Inter', sans-serif; box-shadow: 0 1px 2px rgba(0,0,0,0.02); }
        .pay-method.active { background: #fff7ed; border-color: #f97316; color: #c2410c; box-shadow: 0 4px 6px rgba(249,115,22,0.1); transform: translateY(-2px); }
        .pay-method:hover:not(.active) { border-color: #fbbf24; color: #334155; }

        .checkout-card { background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .checkout-row { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; }
        .checkout-row.received { background: #ffffff; border-bottom: 1px solid #e2e8f0; }
        .checkout-row.change { background: #f0fdf4; }
        .checkout-row.change.negative { background: #fef2f2; }
        
        .checkout-row span.lbl { font-size: 1.1rem; color: #475569; font-weight: 700; flex-shrink: 0; }
        .checkout-row.change span.lbl { color: #15803d; }
        .checkout-row.change.negative span.lbl { color: #b91c1c; }

        .checkout-row input { flex: 1; text-align: right; background: transparent; border: none; font-size: 1.6rem; font-weight: 800; color: #1e293b; outline: none; font-family: 'Inter', sans-serif; cursor: pointer; margin-left: 10px; width: 100%; min-width: 0; }
        .checkout-row input:focus { color: #f97316; }
        .checkout-row.change .val { font-size: 1.6rem; font-weight: 800; color: #166534; }
        .checkout-row.change.negative .val { color: #b91c1c; }

        /* Numpad specific */
        .numpad-btn { padding: 18px 10px; font-size: 1.6rem; font-weight: 700; border: none; border-radius: 12px; background: #f1f5f9; color: #1e293b; cursor: pointer; transition: all .1s; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-family: 'Inter', sans-serif; user-select: none; }
        .numpad-btn:active { transform: scale(0.95); background: #e2e8f0; }

        /* Checkout button */
        .btn-checkout { width: 100%; margin-top: auto; padding: 16px; background: linear-gradient(135deg, #f97316, #d97706); border: none; border-radius: 12px; color: white; font-size: 1.2rem; font-weight: 800; cursor: pointer; transition: all .2s; font-family: 'Inter', sans-serif; letter-spacing: 0.5px; box-shadow: 0 4px 6px rgba(234,88,12,0.2); }
        .btn-checkout:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(234,88,12,0.3); background: linear-gradient(135deg, #ea580c, #c2410c); }
        .btn-checkout:disabled { opacity: 0.6; cursor: not-allowed; transform: none; background: #cbd5e1; box-shadow: none; color: #64748b; }
        .btn-clear { width: 100%; padding: 12px; background: transparent; border: 2px solid #cbd5e1; border-radius: 10px; color: #64748b; font-size: 0.95rem; cursor: pointer; margin-top: 12px; transition: all .2s; font-family: 'Inter', sans-serif; font-weight: 700; }
        .btn-clear:hover { border-color: #ef4444; color: #ef4444; background: #fef2f2; }

        /* Toast */
        .toast { position: fixed; top: 20px; right: 20px; z-index: 9999; padding: 14px 20px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; display: none; animation: slideIn .3s ease; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .toast.success { background: #10b981; border: 1px solid #059669; color: white; }
        .toast.error { background: #ef4444; border: 1px solid #dc2626; color: white; }
        @keyframes slideIn { from { opacity:0; transform: translateX(30px); } to { opacity:1; transform: translateX(0); } }

        /* Modal overlay */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 1000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
        .modal-overlay.active { display: flex; }
        .modal { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 32px; max-width: 420px; width: 90%; animation: modalIn .25s ease; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); text-align: center; }
        @keyframes modalIn { from { opacity:0; transform: scale(0.95); } to { opacity:1; transform: scale(1); } }
        .modal h3 { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
        .modal p { font-size: 0.95rem; color: #64748b; margin-bottom: 24px; }
        .modal .invoice-big { font-size: 1.6rem; font-weight: 700; color: #ea580c; font-family: monospace; margin-bottom: 6px; }
        .modal .total-big { font-size: 2.5rem; font-weight: 800; color: #16a34a; margin-bottom: 6px; letter-spacing: -1px; }
        .modal .change-big { font-size: 1rem; color: #64748b; margin-bottom: 20px; font-weight: 500; }
        .modal .email-sent-badge { display: inline-flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; font-size: 0.8rem; font-weight: 600; padding: 8px 16px; border-radius: 20px; margin-bottom: 24px; }
        .modal .modal-btns { display: flex; gap: 12px; }
        .modal .btn-receipt { flex: 1; padding: 14px; background: #1e293b; border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; font-size: 0.95rem; transition: background .2s; }
        .modal .btn-receipt:hover { background: #0f172a; }
        .modal .btn-new { flex: 1; padding: 14px; background: linear-gradient(135deg, #f97316, #d97706); border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; font-size: 0.95rem; transition: all .2s; }
        .modal .btn-new:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(234,88,12,0.2); }
    </style>
</head>
<body>

{{-- Top Bar --}}
<div class="topbar">
    <div class="logo">
        @if(setting('company_logo'))
            <img src="{{ Storage::url(setting('company_logo')) }}" alt="Logo" style="height: 28px; border-radius: 6px;">
        @else
            ⚡
        @endif
        {{ setting('store_name', config('app.name')) }}
    </div>
    <div class="info">
        <span>Cashier: <b>{{ auth()->user()->name }}</b></span>
        <span>Date: <b>{{ now()->format('d M Y') }}</b></span>
        <span>Time: <b id="liveClock"></b></span>
    </div>
    <div class="nav-links">
        <button onclick="toggleTouchMode()" id="touchModeBtn" style="color:#64748b; font-weight:700; background:transparent; border:1px solid transparent;">⌨️ Keyboard Mode</button>
        <button onclick="toggleFullScreen()" id="fullScreenBtn">🔲 Full Screen</button>
        <button onclick="openHeldSalesModal()">📂 Parked Sales</button>
        <a href="{{ route('pos.history') }}">📋 Sales History</a>
        <a href="{{ route('products.index') }}">📦 Products</a>
        <a href="{{ route('dashboard') }}">🏠 Dashboard</a>
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="color:#f87171;">Logout</a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
    </div>
</div>

{{-- Main POS Layout --}}
<div class="pos-layout">

    {{-- LEFT: Product Search --}}
    <div class="product-panel">
        <div class="search-section">
            <div class="search-wrap">
                <span class="search-icon">🔍</span>
                <input type="text" id="searchInput" placeholder="Search by product name or scan barcode..." autocomplete="off" autofocus>
            </div>
            <div class="search-hint">Press Enter or scan barcode to add instantly · Click a product card to add to cart</div>
        </div>
        <div class="product-results">
            <div id="productGrid" class="product-grid"></div>
            <div id="noResults" class="no-results" style="display:none;">
                <div class="icon">📭</div>
                <p>No products found</p>
            </div>
            <div id="startHint" class="no-results">
                <div class="icon">🛒</div>
                <p>Search a product or scan a barcode to begin</p>
            </div>
        </div>
    </div>

    {{-- RIGHT: Cart --}}
    <div class="cart-panel">
        <div class="cart-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>🛒 Current Sale</h3>
                <button onclick="holdSale()" style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; color: #475569;">⏸️ Hold Sale</button>
            </div>
            <div class="invoice-badge" id="invoiceLabel">Invoice will be generated on checkout</div>
        </div>

        {{-- Customer Info --}}
        <div class="cart-customer">
            <div class="cust-row">
                <input type="hidden" id="custId">
                <input type="text" id="custName" placeholder="👤 Customer name (optional)">
                <input type="text" id="custPhone" placeholder="📞 Phone (optional)" oninput="searchCustomer()">
            </div>
            <div class="email-row">
                <input type="email" id="custEmail" placeholder="📧 Email for e-bill (optional)" oninput="toggleSendEmail()">
                <label class="ebill-label" id="sendEmailLabel" style="display:none;">
                    <input type="checkbox" id="sendEmailCheck" checked> Send E-Bill
                </label>
            </div>
            <div id="loyaltyBadge" style="display:none; margin-top:6px; font-size:0.75rem; color:#d97706; font-weight:600;">
                ⭐ Available Points: <span id="availPoints">0</span>
            </div>
        </div>

        {{-- Cart Items --}}
        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <span class="icon">🛍️</span>
                Add products from the left panel
            </div>
        </div>
    </div>

    {{-- RIGHT: Payment --}}
    <div class="payment-panel">
        <div class="cart-header" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; padding: 12px 16px;">
            <h3 style="color: #1e293b; font-size: 1.1rem; letter-spacing: 0px;">💳 Payment & Checkout</h3>
        </div>

        {{-- Totals --}}
        <div class="cart-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span class="val" id="subtotalDisp">0.00</span>
            </div>

            {{-- Discount --}}
            <div class="discount-row">
                <label style="width: 50px;">Disc:</label>
                <select id="discountType" style="width: 70px;">
                    <option value="fixed">Fixed</option>
                    <option value="percent">%</option>
                </select>
                <input type="text" id="discountValue" value="0" placeholder="0" oninput="recalc()" onclick="openNumpad('discountValue', 'Enter Discount Amount')">
                <label style="margin-left: 10px;">Tax %:</label>
                <input type="text" id="taxPercent" value="0" placeholder="0" oninput="recalc()" style="width:70px;" onclick="openNumpad('taxPercent', 'Enter Tax Percentage')">
            </div>

            {{-- Loyalty Points --}}
            <div class="discount-row">
                <label style="width: 50px;">Pts:</label>
                <input type="text" id="pointsRedeemed" value="0" placeholder="0" oninput="recalc()" onclick="openNumpad('pointsRedeemed', 'Redeem Points')">
                <span style="font-size: 0.85rem; color:#64748b; font-weight:600; margin-left:4px;">Redeem Points</span>
            </div>

            {{-- Promo Code --}}
            <div class="discount-row" style="margin-top: 16px;">
                <label style="width: 50px;">Promo:</label>
                <input type="text" id="promoCodeInput" placeholder="Code" style="text-transform: uppercase;">
                <button onclick="applyPromoCode()" style="padding: 10px 14px; border:none; border-radius:8px; background:#4f46e5; color:white; font-weight:700; font-size:0.95rem; cursor:pointer; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2); transition: all .2s;">Apply</button>
            </div>

            <div class="total-row">
                <span>Discount</span>
                <span class="val" id="discountDisp">- 0.00</span>
            </div>
            <div class="total-row">
                <span>Tax</span>
                <span class="val" id="taxDisp">+ 0.00</span>
            </div>
            <div class="total-row main">
                <span>TOTAL</span>
                <span class="val" id="totalDisp">0.00</span>
            </div>
        </div>

        {{-- Payment --}}
        <div class="payment-section">
            <div class="payment-methods">
                <button class="pay-method active" onclick="setPayMethod('cash', this)">💵 Cash</button>
                <button class="pay-method" onclick="setPayMethod('card', this)">💳 Card</button>
                <button class="pay-method" onclick="setPayMethod('other', this)">📱 Other</button>
            </div>

            <div class="checkout-card">
                <div class="checkout-row received">
                    <span class="lbl">Received:</span>
                    <input type="text" id="paidAmount" placeholder="0.00" oninput="calcChange()" onclick="openNumpad('paidAmount', 'Enter Paid Amount')">
                </div>
                <div class="checkout-row change" id="changeDisplay">
                    <span class="lbl">Change</span>
                    <span class="val" id="changeDisp">0.00</span>
                </div>
            </div>

            <button class="btn-checkout" id="checkoutBtn" onclick="processCheckout()" disabled>
                ⚡ Complete Sale
            </button>
            <button class="btn-clear" onclick="clearCart()">🗑 Clear Cart</button>
        </div>
    </div>
</div>

{{-- Success Modal --}}
<div class="modal-overlay" id="successModal">
    <div class="modal">
        <h3>✅ Sale Completed!</h3>
        <div class="invoice-big" id="modalInvoice"></div>
        <div class="total-big" id="modalTotal"></div>
        <div class="change-big" id="modalChange"></div>
        <p id="modalCustomer"></p>
        <div id="modalEmailBadge" style="display:none;" class="email-sent-badge">📧 E-Bill sent!</div>
        <div class="modal-btns">
            <button class="btn-receipt" id="btnReceipt" onclick="openReceipt()">🖨 Print Receipt</button>
            <button class="btn-new" onclick="newSale()">+ New Sale</button>
        </div>
    </div>
</div>

{{-- Confirm Modal --}}
<div class="modal-overlay" id="confirmModal">
    <div class="modal" style="max-width: 350px;">
        <h3>⚠️ Clear Cart?</h3>
        <p style="margin-bottom: 24px;">Are you sure you want to remove all items?</p>
        <div class="modal-btns">
            <button class="btn-receipt" onclick="closeConfirmModal()">Cancel</button>
            <button class="btn-new" onclick="confirmClearCart()" style="background: #ef4444; box-shadow: none;">Yes, Clear</button>
        </div>
    </div>
</div>

{{-- Numpad Modal --}}
<div class="modal-overlay" id="numpadModal">
    <div class="modal" style="max-width: 320px; padding: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 id="numpadTitle" style="font-size: 1.2rem; margin: 0; color:#475569;">Enter Amount</h3>
            <button onclick="document.getElementById('numpadModal').classList.remove('active')" style="background:none; border:none; font-size:1.5rem; color:#94a3b8; cursor:pointer;">✕</button>
        </div>
        <input type="text" id="numpadDisplay" readonly style="width: 100%; text-align: right; font-size: 2.2rem; font-weight: 800; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 2px solid #cbd5e1; background: #f8fafc; color: #1e293b;">
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
            <button class="numpad-btn" onclick="numpadPress('1')">1</button>
            <button class="numpad-btn" onclick="numpadPress('2')">2</button>
            <button class="numpad-btn" onclick="numpadPress('3')">3</button>
            <button class="numpad-btn" onclick="numpadPress('4')">4</button>
            <button class="numpad-btn" onclick="numpadPress('5')">5</button>
            <button class="numpad-btn" onclick="numpadPress('6')">6</button>
            <button class="numpad-btn" onclick="numpadPress('7')">7</button>
            <button class="numpad-btn" onclick="numpadPress('8')">8</button>
            <button class="numpad-btn" onclick="numpadPress('9')">9</button>
            <button class="numpad-btn" onclick="numpadPress('.')">.</button>
            <button class="numpad-btn" onclick="numpadPress('0')">0</button>
            <button class="numpad-btn" onclick="numpadPress('back')" style="background: #e2e8f0;">⌫</button>
            <button class="numpad-btn" onclick="numpadClear()" style="background: #fee2e2; color: #ef4444; grid-column: span 1;">C</button>
            <button class="numpad-btn" onclick="numpadEnter()" style="background: #10b981; color: white; grid-column: span 2;">Enter</button>
        </div>
    </div>
</div>

{{-- Held Sales Modal --}}
<div class="modal-overlay" id="heldSalesModal">
    <div class="modal" style="max-width: 600px;">
        <h3>📂 Parked Sales</h3>
        <p style="margin-bottom: 24px;">Select a sale to resume or delete it.</p>
        <div id="heldSalesList" style="text-align: left; max-height: 300px; overflow-y: auto; margin-bottom: 16px;">
            <!-- Rendered via JS -->
        </div>
        <div class="modal-btns">
            <button class="btn-receipt" onclick="closeHeldSalesModal()">Close</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let cart = [];
let payMethod = 'cash';
let lastReceiptUrl = '';

// Live clock
let liveClockInterval = window.liveClockInterval || null;
if (liveClockInterval) clearInterval(liveClockInterval);

function updateClock() {
    const now = new Date();
    const el = document.getElementById('liveClock');
    if (el) el.textContent = now.toLocaleTimeString();
}
liveClockInterval = setInterval(updateClock, 1000);
window.liveClockInterval = liveClockInterval;
updateClock();

// Search
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    searchTimer = setTimeout(() => searchProducts(q), 200);
});

// Full Screen Toggle
function toggleFullScreen() {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            showToast(`Error attempting to enable full-screen mode: ${err.message}`, 'error');
        });
        document.getElementById('fullScreenBtn').innerHTML = '🔳 Exit Full Screen';
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            document.getElementById('fullScreenBtn').innerHTML = '🔲 Full Screen';
        }
    }
}

// Barcode scanner: fast input capture from anywhere on the page
let barcodeBuffer = '';
let barcodeTimer;

function handleBarcode(e) {
    // If typing in an input/textarea, handle normally
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
        if (e.target.id === 'searchInput' && e.key === 'Enter') {
            const q = e.target.value.trim();
            if (!q) return;
            clearTimeout(searchTimer);
            searchProducts(q, true);
            e.preventDefault();
        }
        return;
    }

    if (e.key === 'Enter') {
        if (barcodeBuffer.length >= 2) {
            searchProducts(barcodeBuffer, true);
        }
        barcodeBuffer = '';
        clearTimeout(barcodeTimer);
        e.preventDefault();
        return;
    }

    // Only allow alphanumeric and basic symbols for barcodes
    if (e.key.length === 1 && /[a-zA-Z0-9\-_]/.test(e.key)) {
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimer);
        // Scanners type very fast (usually <20ms per char).
        // 50ms timeout clears buffer if typed slowly by a human.
        barcodeTimer = setTimeout(() => {
            barcodeBuffer = '';
        }, 50);
    }
}

document.removeEventListener('keydown', handleBarcode);
document.addEventListener('keydown', handleBarcode);

function showStartHint() {
    // Replaced by default product grid view, but kept for compatibility
    document.getElementById('startHint').style.display = 'none';
}

function searchProducts(q, addFirst = false) {
    fetch(`/ajax/products/search?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(products => {
            document.getElementById('startHint').style.display = 'none';
            const grid = document.getElementById('productGrid');
            const noRes = document.getElementById('noResults');

            if (products.length === 0) { grid.innerHTML = ''; noRes.style.display = 'block'; return; }
            noRes.style.display = 'none';

            if (addFirst) { addToCart(products[0]); document.getElementById('searchInput').value = ''; searchProducts(''); return; }

            grid.innerHTML = products.map(p => {
                let imgHtml = p.image 
                    ? `<div class="icon" style="background-image: url('/storage/${p.image}'); background-size: cover; background-position: center; width: 48px; height: 48px; border-radius: 8px;"></div>`
                    : `<div class="icon">📦</div>`;
                return `
                <div class="product-card ${p.stock_quantity <= 0 ? 'out-of-stock' : ''}" onclick="addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                    ${imgHtml}
                    <div class="pname">${p.name}</div>
                    <div class="psku">${p.barcode ? p.barcode : ''}</div>
                    <div class="pprice">${parseFloat(p.selling_price).toFixed(2)}</div>
                    <div class="pstock ${p.stock_quantity <= 10 ? 'low' : ''}">Stock: ${p.stock_quantity} ${p.unit}</div>
                </div>
            `}).join('');
        });
}

function addToCart(product) {
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.qty >= product.stock_quantity) { showToast('Insufficient stock!', 'error'); return; }
        existing.qty++;
        existing.subtotal = existing.qty * existing.unit_price;
    } else {
        if (product.stock_quantity <= 0) { showToast('Out of stock!', 'error'); return; }
        cart.push({
            id: product.id,
            name: product.name,
            barcode: product.barcode,
            unit_price: parseFloat(product.selling_price),
            qty: 1,
            stock: product.stock_quantity,
            subtotal: parseFloat(product.selling_price),
        });
    }
    renderCart();
    showToast(`${product.name} added`, 'success');
}

function renderCart() {
    const container = document.getElementById('cartItems');

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="cart-empty" id="cartEmpty">
                <span class="icon">🛍️</span>
                Add products from the left panel
            </div>`;
        document.getElementById('checkoutBtn').disabled = true;
        recalc();
        return;
    }

    const html = cart.map((item, idx) => `
        <div class="cart-item">
            <div class="item-info">
                <div class="item-name">${item.name}</div>
                <div class="item-price">${item.unit_price.toFixed(2)} × ${item.qty}</div>
            </div>
            <div class="qty-ctrl">
                <button class="qty-btn" onclick="changeQty(${idx}, -1)">−</button>
                <span class="qty-val">${item.qty}</span>
                <button class="qty-btn" onclick="changeQty(${idx}, 1)">+</button>
            </div>
            <div class="item-total">${item.subtotal.toFixed(2)}</div>
            <button class="remove-btn" onclick="removeItem(${idx})">✕</button>
        </div>
    `).join('');

    container.innerHTML = html;
    recalc();
    document.getElementById('checkoutBtn').disabled = false;
}

function changeQty(idx, delta) {
    const item = cart[idx];
    const newQty = item.qty + delta;
    if (newQty <= 0) { removeItem(idx); return; }
    if (newQty > item.stock) { showToast('Insufficient stock!', 'error'); return; }
    item.qty = newQty;
    item.subtotal = item.qty * item.unit_price;
    renderCart();
}

function removeItem(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function clearCart() {
    if (cart.length === 0) return;
    document.getElementById('confirmModal').classList.add('active');
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.remove('active');
}

function confirmClearCart() {
    cart = [];
    renderCart();
    closeConfirmModal();
}

function recalc() {
    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);
    const discType = document.getElementById('discountType').value;
    const discVal  = parseFloat(document.getElementById('discountValue').value) || 0;
    const taxPct   = parseFloat(document.getElementById('taxPercent').value) || 0;
    const ptsRedeemed = parseInt(document.getElementById('pointsRedeemed').value) || 0;
    const availPts = parseInt(document.getElementById('availPoints').textContent) || 0;

    if (ptsRedeemed > availPts) {
        document.getElementById('pointsRedeemed').value = availPts;
    }

    const finalPts = parseInt(document.getElementById('pointsRedeemed').value) || 0;
    const ptsValue = finalPts * {{ setting('loyalty_redemption_value', 1) }};

    const discAmt  = discType === 'percent' ? subtotal * discVal / 100 : discVal;
    let afterDisc= subtotal - discAmt - ptsValue;
    if (afterDisc < 0) afterDisc = 0;
    
    const taxAmt   = afterDisc * taxPct / 100;
    const total    = afterDisc + taxAmt;

    document.getElementById('subtotalDisp').textContent = subtotal.toFixed(2);
    document.getElementById('discountDisp').textContent = '- ' + (discAmt + ptsValue).toFixed(2);
    document.getElementById('taxDisp').textContent      = '+ ' + taxAmt.toFixed(2);
    document.getElementById('totalDisp').textContent    = total.toFixed(2);

    calcChange();
}

function calcChange() {
    const total = parseFloat(document.getElementById('totalDisp').textContent) || 0;
    const paid  = parseFloat(document.getElementById('paidAmount').value) || 0;
    const change = paid - total;
    const disp  = document.getElementById('changeDisplay');
    document.getElementById('changeDisp').textContent = Math.abs(change).toFixed(2);
    if (change < 0) {
        disp.classList.add('negative');
        document.getElementById('changeDisp').previousElementSibling.textContent = 'Due';
    } else {
        disp.classList.remove('negative');
        document.getElementById('changeDisp').previousElementSibling.textContent = 'Change';
    }
}

function setPayMethod(method, btn) {
    payMethod = method;
    document.querySelectorAll('.pay-method').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (method !== 'cash') {
        const total = parseFloat(document.getElementById('totalDisp').textContent) || 0;
        document.getElementById('paidAmount').value = total.toFixed(2);
        calcChange();
    }
}

let appliedPromo = null;

async function applyPromoCode() {
    const code = document.getElementById('promoCodeInput').value.trim();
    if (!code) return;

    const subtotal = cart.reduce((s, i) => s + i.subtotal, 0);

    try {
        const res = await fetch('/ajax/promos/validate', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ code: code, cart_total: subtotal })
        });
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            appliedPromo = code;
            document.getElementById('discountType').value = data.type;
            document.getElementById('discountValue').value = data.value;
            document.getElementById('discountType').disabled = true;
            document.getElementById('discountValue').disabled = true;
            recalc();
        } else {
            showToast(data.message, 'error');
            appliedPromo = null;
            document.getElementById('discountType').disabled = false;
            document.getElementById('discountValue').disabled = false;
        }
    } catch (err) {
        showToast('Error validating promo code.', 'error');
    }
}

async function holdSale() {
    if (cart.length === 0) {
        showToast('Cart is empty', 'error');
        return;
    }
    const note = prompt("Enter a reference note for this parked sale (e.g. 'Customer forgot wallet'):");
    if (!note) return;

    try {
        const payload = {
            reference_note: note,
            customer_id: document.getElementById('custId').value || null,
            items: cart.map(i => ({ id: i.id, qty: i.qty }))
        };

        const res = await fetch('/pos/hold', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message, 'success');
            cart = [];
            renderCart();
            // Reset customer & promo
            document.getElementById('custId').value = '';
            document.getElementById('custName').value = '';
            document.getElementById('custPhone').value = '';
            document.getElementById('loyaltyBadge').style.display = 'none';
            appliedPromo = null;
            document.getElementById('promoCodeInput').value = '';
            document.getElementById('discountType').disabled = false;
            document.getElementById('discountValue').disabled = false;
        } else {
            showToast(data.message, 'error');
        }
    } catch (e) {
        showToast('Error parking sale.', 'error');
    }
}

async function openHeldSalesModal() {
    document.getElementById('heldSalesModal').classList.add('active');
    document.getElementById('heldSalesList').innerHTML = '<p>Loading...</p>';
    
    try {
        const res = await fetch('/pos/held-sales');
        const sales = await res.json();
        
        if (sales.length === 0) {
            document.getElementById('heldSalesList').innerHTML = '<p style="color:#64748b;">No parked sales found.</p>';
            return;
        }
        
        let html = '';
        sales.forEach(sale => {
            const customerName = sale.customer ? sale.customer.name : 'Walk-in Customer';
            const total = parseFloat(sale.total_amount).toFixed(2);
            html += `
                <div style="padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-weight: 600; color: #1e293b;">${sale.reference_note}</div>
                        <div style="font-size: 0.85rem; color: #64748b;">${customerName} • Rs. ${total} • ${new Date(sale.created_at).toLocaleString()}</div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button onclick="resumeHeldSale(${sale.id})" style="padding: 6px 12px; background: #10b981; border: none; border-radius: 6px; color: white; cursor: pointer; font-size: 0.8rem; font-weight: 600;">Resume</button>
                        <button onclick="deleteHeldSale(${sale.id})" style="padding: 6px 12px; background: #ef4444; border: none; border-radius: 6px; color: white; cursor: pointer; font-size: 0.8rem; font-weight: 600;">Delete</button>
                    </div>
                </div>
            `;
        });
        document.getElementById('heldSalesList').innerHTML = html;
    } catch (e) {
        document.getElementById('heldSalesList').innerHTML = '<p style="color:red;">Error loading parked sales.</p>';
    }
}

function closeHeldSalesModal() {
    document.getElementById('heldSalesModal').classList.remove('active');
}

async function resumeHeldSale(id) {
    // In a real scenario, you'd fetch the sale items. Since we already loaded them in getHeldSales
    // we can re-fetch just that sale. For simplicity, let's fetch all and filter.
    const res = await fetch('/pos/held-sales');
    const sales = await res.json();
    const sale = sales.find(s => s.id === id);
    if (!sale) return;

    cart = sale.items.map(i => ({
        id: i.product_id,
        name: i.product.name,
        unit_price: parseFloat(i.unit_price),
        qty: i.quantity,
        subtotal: parseFloat(i.subtotal),
        stock: i.product.stock_quantity || 999 
    }));

    if (sale.customer) {
        document.getElementById('custId').value = sale.customer.id;
        document.getElementById('custName').value = sale.customer.name;
        document.getElementById('custPhone').value = sale.customer.phone;
        document.getElementById('availPoints').textContent = sale.customer.loyalty_points;
        document.getElementById('loyaltyBadge').style.display = 'block';
    }

    renderCart();
    closeHeldSalesModal();
    // Delete the held sale from DB now that it's in the cart
    await fetch('/pos/held-sales/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
}

async function deleteHeldSale(id) {
    if (!confirm("Are you sure you want to delete this parked sale?")) return;
    try {
        await fetch('/pos/held-sales/' + id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf } });
        openHeldSalesModal(); // refresh list
    } catch (e) {
        showToast('Error deleting parked sale', 'error');
    }
}

function processCheckout() {
    if (cart.length === 0) return;
    const total = parseFloat(document.getElementById('totalDisp').textContent);
    const paid  = parseFloat(document.getElementById('paidAmount').value) || 0;

    if (payMethod === 'cash' && paid < total) {
        showToast('Paid amount is less than total!', 'error'); return;
    }

    document.getElementById('checkoutBtn').disabled = true;
    document.getElementById('checkoutBtn').textContent = '⏳ Processing...';

    const payload = {
        items: cart.map(i => ({ id: i.id, qty: i.qty })),
        discount_type: document.getElementById('discountType').value,
        discount_value: parseFloat(document.getElementById('discountValue').value) || 0,
        tax_percent: parseFloat(document.getElementById('taxPercent').value) || 0,
        paid_amount: paid,
        payment_method: payMethod,
        customer_id: document.getElementById('custId').value,
        customer_name: document.getElementById('custName').value,
        customer_phone: document.getElementById('custPhone').value,
        customer_email: document.getElementById('custEmail').value,
        send_email: document.getElementById('sendEmailCheck').checked ? 1 : 0,
        points_redeemed: parseInt(document.getElementById('pointsRedeemed').value) || 0,
        promo_code: appliedPromo
    };

    fetch('/pos/checkout', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            lastReceiptUrl = data.receipt_url;
            document.getElementById('modalInvoice').textContent = data.invoice;
            document.getElementById('modalTotal').textContent = total.toFixed(2);
            document.getElementById('modalChange').textContent = 'Change: ' + (paid - total).toFixed(2);
            const cust = document.getElementById('custName').value;
            document.getElementById('modalCustomer').textContent = cust ? `Customer: ${cust}` : '';
            // Show email sent badge
            const emailBadge = document.getElementById('modalEmailBadge');
            if (data.email_sent && data.email) {
                emailBadge.textContent = '📧 E-Bill sent to ' + data.email;
                emailBadge.style.display = 'inline-flex';
            } else {
                emailBadge.style.display = 'none';
            }
            document.getElementById('successModal').classList.add('active');
        } else {
            showToast(data.message || 'Checkout failed', 'error');
            document.getElementById('checkoutBtn').disabled = false;
            document.getElementById('checkoutBtn').textContent = '⚡ Complete Sale';
        }
    })
    .catch(() => {
        showToast('Network error. Please try again.', 'error');
        document.getElementById('checkoutBtn').disabled = false;
        document.getElementById('checkoutBtn').textContent = '⚡ Complete Sale';
    });
}

function openReceipt() {
    if (!lastReceiptUrl) {
        showToast('Receipt URL not found.', 'error');
        return;
    }
    // Open in a compact popup window instead of a full new tab
    const printWindow = window.open(lastReceiptUrl, 'Receipt', 'width=400,height=600,toolbar=0,scrollbars=1,status=0');
    if (printWindow) {
        printWindow.focus();
    }
}

function newSale() {
    document.getElementById('successModal').classList.remove('active');
    cart = [];
    document.getElementById('custName').value = '';
    document.getElementById('custPhone').value = '';
    document.getElementById('custId').value = '';
    document.getElementById('custEmail').value = '';
    document.getElementById('sendEmailCheck').checked = false;
    document.getElementById('sendEmailLabel').style.display = 'none';
    document.getElementById('loyaltyBadge').style.display = 'none';
    document.getElementById('availPoints').textContent = '0';
    document.getElementById('pointsRedeemed').value = '0';
    document.getElementById('discountValue').value = '0';
    document.getElementById('taxPercent').value = '0';
    document.getElementById('paidAmount').value = '';
    document.getElementById('promoCodeInput').value = '';
    document.getElementById('discountType').disabled = false;
    document.getElementById('discountValue').disabled = false;
    appliedPromo = null;
    document.getElementById('checkoutBtn').textContent = '⚡ Complete Sale';
    renderCart();
    searchProducts('');
    document.getElementById('searchInput').focus();
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.className = `toast ${type}`;
    t.style.display = 'block';
    setTimeout(() => { t.style.display = 'none'; }, 3000);
}

function toggleSendEmail() {
    const email = document.getElementById('custEmail').value.trim();
    const label = document.getElementById('sendEmailLabel');
    label.style.display = email ? 'flex' : 'none';
}

let custTimer;
function searchCustomer() {
    clearTimeout(custTimer);
    const phone = document.getElementById('custPhone').value.trim();
    if(phone.length < 9) {
        document.getElementById('loyaltyBadge').style.display = 'none';
        document.getElementById('availPoints').textContent = '0';
        document.getElementById('pointsRedeemed').value = '0';
        document.getElementById('custId').value = '';
        recalc();
        return;
    }
    custTimer = setTimeout(() => {
        fetch('/ajax/customers/search?q=' + phone)
        .then(r => r.json())
        .then(data => {
            if(data && data.length > 0) {
                const c = data[0];
                document.getElementById('custId').value = c.id;
                document.getElementById('custName').value = c.name;
                if(c.email) document.getElementById('custEmail').value = c.email;
                document.getElementById('availPoints').textContent = c.loyalty_points || 0;
                document.getElementById('loyaltyBadge').style.display = 'block';
                toggleSendEmail();
            }
        });
    }, 400);
}

// --- Numpad Logic ---
let isTouchMode = false;
let numpadTargetInput = null;

function toggleTouchMode() {
    isTouchMode = !isTouchMode;
    
    const btn = document.getElementById('touchModeBtn');
    if (isTouchMode) {
        btn.innerHTML = '👆 Touch Mode: ON';
        btn.style.color = '#0ea5e9';
        btn.style.background = '#f0f9ff';
        btn.style.borderColor = '#bae6fd';
    } else {
        btn.innerHTML = '⌨️ Keyboard Mode';
        btn.style.color = '#64748b';
        btn.style.background = 'transparent';
        btn.style.borderColor = 'transparent';
    }

    // Toggle readonly state for numpad inputs
    const inputs = ['discountValue', 'taxPercent', 'pointsRedeemed', 'paidAmount'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.readOnly = isTouchMode;
    });
}

function openNumpad(inputId, title) {
    if (!isTouchMode) return;
    numpadTargetInput = document.getElementById(inputId);
    document.getElementById('numpadTitle').textContent = title;
    document.getElementById('numpadDisplay').value = numpadTargetInput.value === '0' ? '' : numpadTargetInput.value;
    document.getElementById('numpadModal').classList.add('active');
}

function numpadPress(val) {
    const disp = document.getElementById('numpadDisplay');
    if (val === 'back') {
        disp.value = disp.value.slice(0, -1);
    } else {
        if (val === '.' && disp.value.includes('.')) return;
        // Don't allow multiple leading zeros
        if (disp.value === '0' && val !== '.') {
            disp.value = val;
            return;
        }
        disp.value += val;
    }
}

function numpadClear() {
    document.getElementById('numpadDisplay').value = '';
}

function numpadEnter() {
    if (numpadTargetInput) {
        // Default empty to 0
        const finalVal = document.getElementById('numpadDisplay').value || '0';
        numpadTargetInput.value = finalVal;
        
        // Trigger the input's oninput event to ensure reactivity
        const event = new Event('input', { bubbles: true });
        numpadTargetInput.dispatchEvent(event);
    }
    document.getElementById('numpadModal').classList.remove('active');
}

// Initial render
renderCart();
searchProducts('');
</script>
</body>
</html>
