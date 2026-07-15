<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Customer Profile</h2>
            <div class="flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition">Edit</a>
                <a href="{{ route('customers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline self-center ml-1">← Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Profile Header --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 flex items-center gap-5">
                <div class="w-16 h-16 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 text-2xl font-bold flex-shrink-0">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $customer->name }}</h3>
                    <div class="flex gap-4 flex-wrap text-sm text-gray-500 dark:text-gray-400 mt-1">
                        @if($customer->phone)<span>📞 {{ $customer->phone }}</span>@endif
                        @if($customer->email)<span>✉️ {{ $customer->email }}</span>@endif
                        @if($customer->city)<span>📍 {{ $customer->city }}</span>@endif
                    </div>
                </div>
                <div>
                    @if($customer->is_active)
                        <span class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">Active</span>
                    @else
                        <span class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full">Inactive</span>
                    @endif
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Spent</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($totalSpent, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Orders</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $totalOrders }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Credit Limit</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($customer->credit_limit, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Outstanding</p>
                    <p class="text-2xl font-bold {{ $customer->balance > 0 ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }} mt-1">{{ number_format($customer->balance, 2) }}</p>
                </div>
            </div>

            {{-- Address & Notes --}}
            @if($customer->address || $customer->notes)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($customer->address)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Address</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $customer->address }}</p>
                </div>
                @endif
                @if($customer->notes)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Notes</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $customer->notes }}</p>
                </div>
                @endif
            </div>
            @endif

            {{-- Payments & Ledger --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Payments & Ledger</h4>
                </div>
                
                <div class="p-6 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <form action="{{ route('customers.payments.store', $customer) }}" method="POST" class="flex flex-col sm:flex-row items-end gap-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Method</label>
                            <select name="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <input type="text" name="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Add Payment</button>
                        </div>
                    </form>
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Method</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Notes</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $payment->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ strtoupper($payment->payment_method) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $payment->notes }}</td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-green-600 dark:text-green-400">-{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">No payments recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Purchase History --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Recent Purchases</h4>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Invoice</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Payment</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Receipt</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentSales as $sale)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3 font-mono text-sm text-indigo-600">{{ $sale->invoice_number }}</td>
                            <td class="px-5 py-3 text-right text-sm font-bold text-gray-900 dark:text-gray-100">{{ number_format($sale->total_amount, 2) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ strtoupper($sale->payment_method) }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $sale->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('pos.receipt', $sale) }}" target="_blank"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-indigo-100 hover:bg-indigo-200 text-indigo-800 text-xs font-medium rounded-lg transition">🖨</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No purchase history found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
