<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">📥 Stock In</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">← Back to Inventory</a>
        </div>
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <style>
            .ts-control { border-color: rgb(209 213 219) !important; padding: 0.5rem 0.75rem !important; border-radius: 0.5rem !important; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important; }
            .dark .ts-control { background-color: rgb(55 65 81) !important; border-color: rgb(75 85 99) !important; color: white !important; }
            .dark .ts-control input { color: white !important; }
            .dark .ts-dropdown { background-color: rgb(55 65 81) !important; color: white !important; border-color: rgb(75 85 99) !important; }
            .dark .ts-dropdown .option:hover, .dark .ts-dropdown .active { background-color: rgb(75 85 99) !important; color: white !important; }
            .ts-wrapper.single .ts-control, .ts-wrapper.single .ts-control input { cursor: pointer; }
        </style>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Use this form to add stock received from a supplier or purchase.</p>

                <form action="{{ route('inventory.stock-in.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product <span class="text-red-500">*</span></label>
                        <select name="product_id" id="product_id" required x-data x-init="new TomSelect($el, {create: false, sortField: {field: 'text', direction: 'asc'}})"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select Product --</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                    data-unit="{{ $p->unit }}"
                                    {{ request('product_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Showroom <span class="text-red-500">*</span></label>
                        <select name="showroom_id" id="showroom_id" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select Showroom to add stock to --</option>
                            @foreach($showrooms as $s)
                                <option value="{{ $s->id }}" {{ request('showroom_id') == $s->id || (auth()->user()->showroom_id == $s->id) ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('showroom_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Quantity to Add <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" value="{{ old('quantity') }}" min="1" required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reference (Supplier / PO Number)</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Supplier ABC, PO-2024-001"
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('reference') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                                  class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('inventory.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</a>
                        <button type="submit"
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                            ✅ Add Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Pre-select if query param provided
        const sel = document.getElementById('product_id');
        if (sel.value) sel.dispatchEvent(new Event('change'));
    </script>
</x-app-layout>
