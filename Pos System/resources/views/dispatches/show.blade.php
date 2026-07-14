<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Dispatch Details: {{ $dispatch->reference_number }}
            </h2>
            <a href="{{ route('dispatches.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">← Back to Dispatches</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">
            
            @if(session('error'))
                <div class="px-4 py-3 bg-red-100 border border-red-400 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Showroom</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $dispatch->showroom->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Created By</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $dispatch->admin->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Status</p>
                        <p class="font-semibold">
                            @if($dispatch->status === 'pending')
                                <span class="text-yellow-600">Pending</span>
                            @elseif($dispatch->status === 'accepted')
                                <span class="text-green-600">Accepted</span>
                            @else
                                <span class="text-red-600">Rejected</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Date</p>
                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $dispatch->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>

                @if($dispatch->notes)
                    <div class="mb-6">
                        <p class="text-xs text-gray-500 uppercase">Notes</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $dispatch->notes }}</p>
                    </div>
                @endif

                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2">Items</h3>
                
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($dispatch->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $item->product->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500 font-mono">{{ $item->product->barcode ?? '—' }}</td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">{{ $item->quantity }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @if($dispatch->status === 'pending' && (!auth()->user()->hasRole('Admin') || auth()->user()->showroom_id == $dispatch->showroom_id))
                    <div class="mt-8 flex justify-end">
                        <form action="{{ route('dispatches.accept', $dispatch->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Are you sure? This will add these items to your stock.');" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                                ✅ Accept & Update Stock
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
