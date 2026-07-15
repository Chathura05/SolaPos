<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Quotations</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left">Quote #</th>
                                <th class="px-4 py-3 text-left">Customer</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                @if(auth()->user()->hasRole('Admin'))
                                    <th class="px-4 py-3 text-left">Created By</th>
                                @endif
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($quotes as $quote)
                            <tr>
                                <td class="px-4 py-3 font-mono text-sm text-indigo-600">{{ $quote->quote_number }}</td>
                                <td class="px-4 py-3">{{ $quote->customer_name ?? 'Walk-in' }}</td>
                                <td class="px-4 py-3 text-right">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($quote->total_amount, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-xs {{ $quote->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($quote->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $quote->created_at->format('d M Y') }}</td>
                                @if(auth()->user()->hasRole('Admin'))
                                    <td class="px-4 py-3 text-sm">{{ $quote->user->name ?? 'Unknown' }}</td>
                                @endif
                                <td class="px-4 py-3 text-right space-x-2">
                                    @if($quote->status === 'pending')
                                        <a href="{{ route('pos.index', ['quote_id' => $quote->id]) }}" class="text-green-600 hover:underline text-sm font-semibold">Convert to Sale</a>
                                    @endif
                                    <a href="{{ route('quotes.show', $quote) }}" target="_blank" class="text-indigo-600 hover:underline text-sm">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $quotes->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
