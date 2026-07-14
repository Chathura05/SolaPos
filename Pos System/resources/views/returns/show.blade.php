<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Return Request: {{ $return->reference_number }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                        Details
                    </h3>
                </div>
                <div class="px-6 py-5">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Showroom</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $return->showroom->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1 text-sm">
                                @if($return->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                @elseif($return->status === 'accepted')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Accepted</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $return->created_at->format('d M Y, h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Admin</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $return->admin->name ?? '—' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $return->notes ?: 'No notes provided.' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden mb-6">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                        Items
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reason</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($return->items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item->product->name ?? 'Unknown' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $item->reason ?: '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($return->status === 'pending' && auth()->user()->hasRole('Admin'))
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Admin Actions</h3>
                    
                    <form action="{{ route('returns.accept', $return->id) }}" method="POST" id="accept-form" class="inline">
                        @csrf
                        <input type="hidden" name="notes" id="accept-notes">
                    </form>

                    <form action="{{ route('returns.reject', $return->id) }}" method="POST" id="reject-form" class="inline">
                        @csrf
                        <input type="hidden" name="notes" id="reject-notes">
                    </form>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Admin Notes (will be appended to existing notes)</label>
                        <textarea id="admin-notes-input" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Reason for rejection or acceptance details..."></textarea>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" onclick="submitAction('accept')" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                            Accept Return & Update Stock
                        </button>
                        <button type="button" onclick="submitAction('reject')" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                            Reject Return
                        </button>
                    </div>

                    <script>
                        function submitAction(action) {
                            const notes = document.getElementById('admin-notes-input').value;
                            if (action === 'accept') {
                                if (confirm('Are you sure you want to accept this return? This will deduct the returned items from the showroom stock.')) {
                                    document.getElementById('accept-notes').value = notes;
                                    document.getElementById('accept-form').submit();
                                }
                            } else if (action === 'reject') {
                                if (confirm('Are you sure you want to reject this return?')) {
                                    document.getElementById('reject-notes').value = notes;
                                    document.getElementById('reject-form').submit();
                                }
                            }
                        }
                    </script>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
