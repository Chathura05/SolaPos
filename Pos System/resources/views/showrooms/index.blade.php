<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Showrooms') }}
            </h2>
            <a href="{{ route('showrooms.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition shadow">
                + Add Showroom
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm" role="alert">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Showroom Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phone</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cashiers</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($showrooms as $showroom)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">{{ $showroom->name }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $showroom->location ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $showroom->phone ?? 'N/A' }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-full text-xs font-medium">
                                    {{ $showroom->users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('showrooms.edit', $showroom) }}" class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-100 rounded-lg transition" title="Edit">✏️</a>
                                    
                                    <form action="{{ route('showrooms.destroy', $showroom) }}" method="POST" onsubmit="return confirm('Delete this showroom? This cannot be undone!');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                <div class="text-4xl mb-2">🏢</div>
                                <p>No showrooms found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $showrooms->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
