<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Supplier Profile</h2>
            <div class="flex gap-2">
                <a href="{{ route('suppliers.edit', $supplier) }}" class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition">Edit</a>
                <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline self-center ml-1">← Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            {{-- Header --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                <div class="flex items-center gap-5">
                    <div class="w-16 h-16 rounded-xl bg-purple-100 dark:bg-purple-900 flex items-center justify-center text-purple-600 text-2xl font-bold flex-shrink-0">
                        🏢
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $supplier->name }}</h3>
                        @if($supplier->company_name)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $supplier->company_name }}</p>
                        @endif
                        <div class="flex gap-4 flex-wrap text-sm text-gray-500 mt-1">
                            @if($supplier->phone)<span>📞 {{ $supplier->phone }}</span>@endif
                            @if($supplier->email)<span>✉️ {{ $supplier->email }}</span>@endif
                            @if($supplier->city)<span>📍 {{ $supplier->city }}</span>@endif
                        </div>
                    </div>
                    @if($supplier->is_active)
                        <span class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">Active</span>
                    @else
                        <span class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full">Inactive</span>
                    @endif
                </div>
            </div>

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Payable Balance</p>
                    <p class="text-2xl font-bold {{ $supplier->payable_balance > 0 ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }} mt-1">
                        {{ number_format($supplier->payable_balance, 2) }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Tax / VAT No.</p>
                    <p class="text-lg font-bold font-mono text-gray-900 dark:text-gray-100 mt-1">{{ $supplier->tax_number ?: '—' }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Added</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $supplier->created_at->format('d M Y') }}</p>
                </div>
            </div>

            {{-- Address & Notes --}}
            @if($supplier->address || $supplier->notes)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($supplier->address)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Address</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $supplier->address }}</p>
                </div>
                @endif
                @if($supplier->notes)
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-5">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Notes</h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $supplier->notes }}</p>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
