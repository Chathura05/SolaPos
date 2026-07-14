<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Promo Codes') }}
            </h2>
            <a href="{{ route('promos.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                + New Promo Code
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3">Code</th>
                                    <th class="px-6 py-3">Type</th>
                                    <th class="px-6 py-3">Value</th>
                                    <th class="px-6 py-3">Min Spend</th>
                                    <th class="px-6 py-3">Uses</th>
                                    <th class="px-6 py-3">Expires At</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($promos as $promo)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">{{ $promo->code }}</td>
                                        <td class="px-6 py-4 uppercase">{{ $promo->type }}</td>
                                        <td class="px-6 py-4 font-semibold">
                                            {{ $promo->type === 'percent' ? $promo->value . '%' : 'Rs. ' . $promo->value }}
                                        </td>
                                        <td class="px-6 py-4">{{ $promo->min_spend > 0 ? 'Rs. ' . $promo->min_spend : 'None' }}</td>
                                        <td class="px-6 py-4">{{ $promo->uses_count }} / {{ $promo->max_uses ?: '∞' }}</td>
                                        <td class="px-6 py-4">
                                            @if($promo->expires_at)
                                                <span class="{{ $promo->expires_at->isPast() ? 'text-red-500' : '' }}">
                                                    {{ $promo->expires_at->format('M d, Y') }}
                                                </span>
                                            @else
                                                Never
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <form action="{{ route('promos.toggle-status', $promo) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="px-2 py-1 rounded text-xs font-semibold
                                                    {{ $promo->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $promo->is_active ? 'Active' : 'Inactive' }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="px-6 py-4 text-right flex justify-end gap-2">
                                            <a href="{{ route('promos.edit', $promo) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit</a>
                                            <form action="{{ route('promos.destroy', $promo) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this promo code?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                            No promo codes found. Click "New Promo Code" to create one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $promos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
