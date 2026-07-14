<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Edit Product</h2>
            <a href="{{ route('products.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">← Back to Products</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf @method('PATCH')

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">Classification</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
                                <select name="category_id" id="category_id" required
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sub-Category</label>
                                <select name="sub_category_id" id="sub_category_id"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">-- Select Sub-Category --</option>
                                    @foreach($subCategories as $sub)
                                        <option value="{{ $sub->id }}" {{ old('sub_category_id', $product->sub_category_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                                @error('sub_category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">Product Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $product->name) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Barcode</label>
                                <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                @error('barcode') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Unit <span class="text-red-500">*</span></label>
                                <select name="unit"
                                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    @foreach(['pcs', 'kg', 'gram', 'litre', 'ml', 'box', 'dozen', 'meter'] as $unit)
                                        <option value="{{ $unit }}" {{ old('unit', $product->unit) === $unit ? 'selected' : '' }}>{{ strtoupper($unit) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                                <textarea name="description" rows="2"
                                          class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $product->description) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">Pricing & Stock</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost Price <span class="text-red-500">*</span></label>
                                <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" step="0.01"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Selling Price <span class="text-red-500">*</span></label>
                                <input type="number" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" step="0.01"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Stock Quantity <span class="text-red-500">*</span></label>
                                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" min="0"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reorder Level <span class="text-red-500">*</span></label>
                                <input type="number" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" min="0"
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">Image & Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Image</label>
                                @if($product->image)
                                    <img src="{{ Storage::url($product->image) }}" class="w-24 h-24 rounded-lg object-cover mb-2">
                                @endif
                                <input type="file" name="image" accept="image/*"
                                       class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex items-center gap-3">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" id="is_active" value="1"
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm"
                                       {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <label for="is_active" class="text-sm text-gray-700 dark:text-gray-300">Product is Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('products.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</a>
                        <button type="submit"
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('category_id').addEventListener('change', function () {
            const catId = this.value;
            const subSelect = document.getElementById('sub_category_id');
            subSelect.innerHTML = '<option value="">-- Select Sub-Category --</option>';
            if (!catId) return;
            fetch(`/ajax/sub-categories?category_id=${catId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.text = sub.name;
                        subSelect.appendChild(opt);
                    });
                });
        });
    </script>
</x-app-layout>
