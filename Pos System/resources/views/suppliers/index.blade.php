<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">🏢 Suppliers</h2>
            <a href="{{ route('suppliers.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Add Supplier
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Suppliers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $totalSuppliers }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Payable</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($totalPayable, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Active</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ \App\Models\Supplier::where('is_active', true)->count() }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, company or phone…"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                        <select name="status"
                                class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 text-sm shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">Filter</button>
                    <a href="{{ route('suppliers.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Supplier</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Contact</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">City</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tax No.</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Payable</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $supplier->name }}</p>
                                @if($supplier->company_name)
                                    <p class="text-xs text-gray-400">{{ $supplier->company_name }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if($supplier->phone)<p class="text-sm text-gray-700 dark:text-gray-300">{{ $supplier->phone }}</p>@endif
                                @if($supplier->email)<p class="text-xs text-gray-400">{{ $supplier->email }}</p>@endif
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $supplier->city ?: '—' }}</td>
                            <td class="px-5 py-3 text-sm font-mono text-gray-500 dark:text-gray-400">{{ $supplier->tax_number ?: '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm font-bold {{ $supplier->payable_balance > 0 ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ number_format($supplier->payable_balance, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                @if($supplier->is_active)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-1">
                                <a href="{{ route('suppliers.show', $supplier) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded-lg transition">View</a>
                                <a href="{{ route('suppliers.edit', $supplier) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs font-medium rounded-lg transition">Edit</a>
                                <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this supplier?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-medium rounded-lg transition">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No suppliers found. <a href="{{ route('suppliers.create') }}" class="text-indigo-600 hover:underline">Add one now.</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $suppliers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
