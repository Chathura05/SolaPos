<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Sales History</h2>
            <a href="{{ route('pos.index') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                ⚡ Open POS Terminal
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Today's Sales</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ number_format($todayTotal, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Today's Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $sales->total() }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">Date</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ now()->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                        <input type="date" name="date" value="{{ request('date', today()->toDateString()) }}"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Invoice #</label>
                        <input type="text" name="invoice" value="{{ request('invoice') }}" placeholder="INV-00001"
                               class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="completed"          {{ request('status') === 'completed'          ? 'selected' : '' }}>Completed</option>
                            <option value="partially_refunded" {{ request('status') === 'partially_refunded' ? 'selected' : '' }}>Partially Refunded</option>
                            <option value="refunded"           {{ request('status') === 'refunded'           ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Filter</button>
                        <a href="{{ route('pos.history') }}" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                    </div>
                    
                    <div class="flex gap-2 ml-auto">
                        <button type="submit" name="export" value="pdf"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition shadow-sm" title="Export as PDF">
                            PDF
                        </button>
                        <button type="submit" name="export" value="csv"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition shadow-sm" title="Export as CSV">
                            CSV
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Invoice</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cashier</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Payment</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date & Time</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3 font-mono text-sm text-indigo-600 dark:text-indigo-400">{{ $sale->invoice_number }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                {{ $sale->customer_name ?: '—' }}
                                @if($sale->customer_phone)
                                    <span class="block text-xs text-gray-400">{{ $sale->customer_phone }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $sale->cashier->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-800' : ($sale->payment_method === 'card' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                    {{ strtoupper($sale->payment_method) }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusClass = match($sale->status) {
                                        'completed'          => 'bg-green-100 text-green-800',
                                        'partially_refunded' => 'bg-amber-100 text-amber-800',
                                        'refunded'           => 'bg-red-100 text-red-800',
                                        default              => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusLabel = match($sale->status) {
                                        'partially_refunded' => 'Part. Refunded',
                                        default              => ucfirst($sale->status),
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $sale->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-5 py-3 text-right space-x-1 whitespace-nowrap">
                                <a href="{{ route('pos.receipt', $sale) }}" target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-xs font-medium rounded-lg transition">
                                    🖨 Receipt
                                </a>
                                <button type="button"
                                        onclick="openEmailModal({{ $sale->id }}, '{{ addslashes($sale->invoice_number) }}', '{{ addslashes($sale->customer_name ?? '') }}')"
                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-medium rounded-lg transition">
                                    📧 E-Bill
                                </button>
                                @role('Admin')
                                @if(in_array($sale->status, ['completed', 'partially_refunded']))
                                <script id="sale-items-{{ $sale->id }}" type="application/json">
                                    {!! $sale->items->toJson() !!}
                                </script>
                                <button type="button"
                                        data-sale-id="{{ $sale->id }}"
                                        data-invoice="{{ $sale->invoice_number }}"
                                        onclick="openRefundModal(this)"
                                        class="inline-flex items-center px-3 py-1.5 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-medium rounded-lg transition">
                                    ↩ Refund
                                </button>
                                @endif
                                @endrole
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">No sales found for this filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $sales->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ── E-Bill Email Modal ── --}}
    <div id="ebillModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center flex">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-md mx-4 animate-fade-in">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">📧 Send E-Bill</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" id="ebillModalSubtitle"></p>
                </div>
                <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer Email Address</label>
                    <input type="email" id="ebillEmailInput"
                           placeholder="customer@example.com"
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <p id="ebillEmailError" class="text-red-500 text-xs mt-1 hidden">Please enter a valid email address.</p>
                </div>

                <div id="ebillFeedback" class="hidden rounded-lg px-4 py-3 text-sm font-medium"></div>

                <div class="flex gap-3 pt-2">
                    <button onclick="closeEmailModal()"
                            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                        Cancel
                    </button>
                    <button id="ebillSendBtn" onclick="sendEBill()"
                            class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition">
                        📧 Send E-Bill
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Refund Modal ── --}}
    <div id="refundModal" class="fixed inset-0 bg-black/60 z-50 hidden items-center justify-center flex">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-2xl mx-4 animate-fade-in max-h-[90vh] flex flex-col">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">↩ Process Refund</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" id="refundModalSubtitle"></p>
                </div>
                <button onclick="closeRefundModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 text-xl leading-none">&times;</button>
            </div>

            <div class="overflow-y-auto flex-1 border border-gray-200 dark:border-gray-700 rounded-lg mb-4">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300">Product</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300">Price</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300">Purchased</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300">Refunded</th>
                            <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300">Return Qty</th>
                        </tr>
                    </thead>
                    <tbody id="refundItemsTable" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <!-- Populated by JS -->
                    </tbody>
                </table>
            </div>

            <div id="refundFeedback" class="hidden rounded-lg px-4 py-3 text-sm font-medium mb-4"></div>

            <div class="flex gap-3 justify-end">
                <button onclick="closeRefundModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg transition">
                    Cancel
                </button>
                <button id="refundSubmitBtn" onclick="submitRefund()"
                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                    Submit Refund
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    let currentSaleId = null;

    function openEmailModal(saleId, invoiceNumber, customerName) {
        currentSaleId = saleId;
        document.getElementById('ebillModalSubtitle').textContent =
            invoiceNumber + (customerName ? ' · ' + customerName : '');
        document.getElementById('ebillEmailInput').value = '';
        document.getElementById('ebillEmailError').classList.add('hidden');
        document.getElementById('ebillFeedback').classList.add('hidden');
        document.getElementById('ebillSendBtn').disabled = false;
        document.getElementById('ebillSendBtn').textContent = '📧 Send E-Bill';
        document.getElementById('ebillModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('ebillEmailInput').focus(), 100);
    }

    function closeEmailModal() {
        document.getElementById('ebillModal').classList.add('hidden');
        currentSaleId = null;
    }

    function sendEBill() {
        const email = document.getElementById('ebillEmailInput').value.trim();
        const errEl = document.getElementById('ebillEmailError');
        const feedback = document.getElementById('ebillFeedback');

        // Basic email validation
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errEl.classList.remove('hidden');
            return;
        }
        errEl.classList.add('hidden');

        const btn = document.getElementById('ebillSendBtn');
        btn.disabled = true;
        btn.textContent = '⏳ Sending...';
        feedback.classList.add('hidden');

        fetch(`/pos/send-receipt/${currentSaleId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ email }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                feedback.textContent = '✅ E-Bill queued! It will be sent to ' + email + ' shortly.';
                feedback.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-700';
                feedback.classList.remove('hidden');
                btn.textContent = '✓ Sent';
            } else {
                feedback.textContent = '❌ Failed: ' + (data.message || 'Unknown error');
                feedback.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-700';
                feedback.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = '📧 Send E-Bill';
            }
        })
        .catch(() => {
            feedback.textContent = '❌ Network error. Please try again.';
            feedback.className = 'rounded-lg px-4 py-3 text-sm font-medium bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-700';
            feedback.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = '📧 Send E-Bill';
        });
    }

    // Close modal on backdrop click
    document.getElementById('ebillModal').addEventListener('click', function(e) {
        if (e.target === this) closeEmailModal();
    });

    // Allow Enter key to submit
    document.getElementById('ebillEmailInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') sendEBill();
    });

    // --- Refund Logic ---
    let currentRefundSaleId = null;

    function openRefundModal(btn) {
        const saleId = btn.getAttribute('data-sale-id');
        const invoiceNumber = btn.getAttribute('data-invoice');
        
        let items = [];
        try {
            const rawJson = document.getElementById('sale-items-' + saleId).textContent;
            items = JSON.parse(rawJson);
        } catch (e) {
            alert('Could not load items for this sale.');
            return;
        }

        currentRefundSaleId = saleId;
        document.getElementById('refundModalSubtitle').textContent = invoiceNumber;
        
        const tbody = document.getElementById('refundItemsTable');
        tbody.innerHTML = '';

        items.forEach(item => {
            const availableToRefund = item.quantity - (item.refunded_quantity || 0);
            const tr = document.createElement('tr');
            
            tr.innerHTML = `
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">${item.product_name}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">${Number(item.unit_price).toFixed(2)}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">${item.quantity}</td>
                <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">${item.refunded_quantity || 0}</td>
                <td class="px-4 py-3 text-center">
                    <input type="number" class="refund-qty-input w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm focus:ring-red-500 focus:border-red-500" 
                           data-item-id="${item.id}" 
                           data-price="${item.unit_price}"
                           min="0" max="${availableToRefund}" value="0" ${availableToRefund === 0 ? 'disabled' : ''}>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById('refundFeedback').classList.add('hidden');
        document.getElementById('refundSubmitBtn').disabled = false;
        document.getElementById('refundSubmitBtn').textContent = 'Submit Refund';
        document.getElementById('refundModal').classList.remove('hidden');
    }

    function closeRefundModal() {
        document.getElementById('refundModal').classList.add('hidden');
        currentRefundSaleId = null;
    }

    function submitRefund() {
        const inputs = document.querySelectorAll('.refund-qty-input');
        const refundItems = [];
        
        inputs.forEach(input => {
            const qty = parseInt(input.value);
            if (qty > 0) {
                refundItems.push({
                    id: input.getAttribute('data-item-id'),
                    qty: qty
                });
            }
        });

        const feedback = document.getElementById('refundFeedback');
        
        if (refundItems.length === 0) {
            feedback.textContent = '❌ Please select at least one item to refund.';
            feedback.className = 'mb-4 rounded-lg px-4 py-3 text-sm font-medium bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-700';
            feedback.classList.remove('hidden');
            return;
        }

        if (!confirm('Are you sure you want to process this refund? Stock will be restored automatically.')) {
            return;
        }

        const btn = document.getElementById('refundSubmitBtn');
        btn.disabled = true;
        btn.textContent = '⏳ Processing...';
        feedback.classList.add('hidden');

        fetch(`/pos/refund-partial/${currentRefundSaleId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({ items: refundItems }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                feedback.textContent = '✅ ' + data.message;
                feedback.className = 'mb-4 rounded-lg px-4 py-3 text-sm font-medium bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-700';
                feedback.classList.remove('hidden');
                btn.textContent = '✓ Refunded';
                setTimeout(() => window.location.reload(), 1500);
            } else {
                feedback.textContent = '❌ Failed: ' + (data.message || 'Unknown error');
                feedback.className = 'mb-4 rounded-lg px-4 py-3 text-sm font-medium bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-700';
                feedback.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Submit Refund';
            }
        })
        .catch(() => {
            feedback.textContent = '❌ Network error. Please try again.';
            feedback.className = 'mb-4 rounded-lg px-4 py-3 text-sm font-medium bg-red-50 dark:bg-red-900/20 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-700';
            feedback.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Submit Refund';
        });
    }

    document.getElementById('refundModal').addEventListener('click', function(e) {
        if (e.target === this) closeRefundModal();
    });

    </script>
    @endpush
</x-app-layout>
