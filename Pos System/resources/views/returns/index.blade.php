<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">🔙 Showroom Returns</h2>
            @if(auth()->user()->showroom_id)
                <a href="{{ route('returns.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Create Return</a>
            @endif
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

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Reference</th>
                            @role('Admin')
                                <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Showroom</th>
                            @endrole
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($returns as $return)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ $return->reference_number }}
                            </td>
                            @role('Admin')
                                <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $return->showroom->name ?? '—' }}</td>
                            @endrole
                            <td class="px-5 py-3">
                                @if($return->status === 'pending')
                                    <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                @elseif($return->status === 'accepted')
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Accepted</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Rejected</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $return->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <a href="{{ route('returns.show', $return->id) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ auth()->user()->hasRole('Admin') ? 5 : 4 }}" class="px-6 py-12 text-center text-gray-500">No returns found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $returns->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
