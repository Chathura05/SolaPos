<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Product Detail</h2>
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}"
                   class="px-4 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 text-sm font-medium rounded-lg transition">Edit</a>
                <a href="{{ route('products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline self-center ml-2">← Back</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                <div class="flex gap-6">
                    @if($product->image)
                        <img src="{{ Storage::url($product->image) }}" class="w-32 h-32 rounded-xl object-cover flex-shrink-0">
                    @else
                        <div class="w-32 h-32 rounded-xl bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-5xl flex-shrink-0">📦</div>
                    @endif
                    <div class="flex-1 space-y-1">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $product->name }}</h3>
                        <p class="text-sm text-gray-500">Barcode: <span class="font-mono">{{ $product->barcode ?: '—' }}</span></p>
                        <p class="text-sm text-gray-500">{{ $product->category->name ?? '-' }} › {{ $product->subCategory->name ?? '-' }}</p>
                        <div class="flex gap-2 mt-2">
                            @if($product->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Inactive</span>
                            @endif
                            @if($product->isLowStock())
                                <span class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded-full">⚠ Low Stock</span>
                            @endif
                        </div>
                    </div>
                </div>

                @if($product->description)
                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $product->description }}</p>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Cost Price</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($product->cost_price, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Selling Price</p>
                    <p class="text-2xl font-bold text-indigo-600 mt-1">{{ number_format($product->selling_price, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Stock</p>
                    <p class="text-2xl font-bold {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }} mt-1">
                        {{ $product->stock_quantity }} <span class="text-sm font-normal">{{ $product->unit }}</span>
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Reorder Level</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $product->reorder_level }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
