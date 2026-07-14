<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">👥 Customers</h2>
            @can('create', App\Models\Customer::class)
            <a href="{{ route('customers.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                + Add Customer
            </a>
            @endcan
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
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Customers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $totalCustomers }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Active</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ $activeCustomers }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Inactive</p>
                    <p class="text-2xl font-bold text-red-500 mt-1">{{ $totalCustomers - $activeCustomers }}</p>
                </div>
            </div>

            {{-- Filters --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4">
                <form method="GET" class="flex gap-3 flex-wrap items-end">
                    <div class="flex-1 min-w-48">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, phone or email…"
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
                    <a href="{{ route('customers.index') }}" class="px-4 py-2 text-sm text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Clear</a>
                </form>
            </div>

            {{-- Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phone</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Email</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">City</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Credit Limit</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-5 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 font-bold text-sm">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $customer->name }}</p>
                                        <p class="text-xs text-gray-400">#{{ $customer->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $customer->phone ?: '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $customer->email ?: '—' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $customer->city ?: '—' }}</td>
                            <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($customer->credit_limit, 2) }}</td>
                            <td class="px-5 py-3">
                                @if($customer->is_active)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right space-x-1">
                                <a href="{{ route('customers.show', $customer) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs font-medium rounded-lg transition">View</a>
                                @can('update', $customer)
                                <a href="{{ route('customers.edit', $customer) }}"
                                   class="inline-flex items-center px-2.5 py-1.5 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-xs font-medium rounded-lg transition">Edit</a>
                                @endcan
                                @can('delete', $customer)
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this customer?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-800 text-xs font-medium rounded-lg transition">Delete</button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                No customers found. <a href="{{ route('customers.create') }}" class="text-indigo-600 hover:underline">Add one now.</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $customers->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
