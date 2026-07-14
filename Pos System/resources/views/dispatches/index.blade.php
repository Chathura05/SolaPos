<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">📦 Showroom Dispatches</h2>
            @role('Admin')
            <a href="{{ route('inventory.import') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Create via Excel</a>
            @endrole
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="px-4 py-3 bg-red-100 border border-red-400 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    @role('Admin')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Showroom</label>
                        <select name="showroom_id"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Showrooms</option>
                            @foreach($showrooms as $sr)
                                <option value="{{ $sr->id }}" {{ request('showroom_id') == $sr->id ? 'selected' : '' }}>
                                    {{ $sr->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endrole
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Filter</button>
                        <a href="{{ route('dispatches.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reference</th>
                            @role('Admin')
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Showroom</th>
                            @endrole
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Created By</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($dispatches as $dispatch)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $dispatch->reference_number }}
                            </td>
                            @role('Admin')
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $dispatch->showroom->name ?? '—' }}</td>
                            @endrole
                            <td class="px-5 py-3">
                                @if($dispatch->status === 'pending')
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                @elseif($dispatch->status === 'accepted')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Accepted</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Rejected</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $dispatch->admin->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $dispatch->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <a href="{{ route('dispatches.show', $dispatch->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View Items</a>
                                
                                @if($dispatch->status === 'pending' && (!auth()->user()->hasRole('Admin') || auth()->user()->showroom_id == $dispatch->showroom_id))
                                    <form action="{{ route('dispatches.accept', $dispatch->id) }}" method="POST" class="inline-block ml-2">
                                        @csrf
                                        <button type="submit" onclick="return confirm('Are you sure you want to accept this dispatch? This will update your stock.');" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded transition">
                                            Accept
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">No dispatches found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $dispatches->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
