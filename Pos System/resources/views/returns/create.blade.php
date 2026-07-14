<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create Return Request</h2>
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <style>
            .ts-control { border-color: rgb(209 213 219) !important; padding: 0.5rem 0.75rem !important; border-radius: 0.375rem !important; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05) !important; }
            .dark .ts-control { background-color: rgb(55 65 81) !important; border-color: rgb(75 85 99) !important; color: white !important; }
            .dark .ts-control input { color: white !important; }
            .dark .ts-dropdown { background-color: rgb(55 65 81) !important; color: white !important; border-color: rgb(75 85 99) !important; }
            .dark .ts-dropdown .option:hover, .dark .ts-dropdown .active { background-color: rgb(75 85 99) !important; color: white !important; }
            .ts-wrapper.single .ts-control, .ts-wrapper.single .ts-control input { cursor: pointer; }
        </style>
    </x-slot>

    <div class="py-8" x-data="returnForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('returns.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Return Details</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">General Notes (Optional)</label>
                        <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>

                    <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mt-6 mb-2">Items to Return</h4>
                    
                    <div class="space-y-4">
                        <template x-for="(item, index) in items" :key="item.id">
                            <div class="flex gap-4 items-end p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex-1" wire:ignore>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product</label>
                                    <select :name="`items[${index}][product_id]`" required
                                        x-init="$nextTick(() => {
                                            new TomSelect($el, {
                                                create: false,
                                                sortField: {field: 'text', direction: 'asc'},
                                                onChange: function(value) { item.product_id = value; }
                                            });
                                        })"
                                    >
                                        <option value="">Select a product...</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->barcode }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                                    <input type="number" min="1" x-model="item.quantity" :name="`items[${index}][quantity]`" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" required>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Reason</label>
                                    <input type="text" x-model="item.reason" :name="`items[${index}][reason]`" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="e.g. Damaged">
                                </div>
                                <div>
                                    <button type="button" @click="removeItem(index)" class="mb-1 px-3 py-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-md transition" title="Remove Item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4">
                        <button type="button" @click="addItem()" class="px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                            + Add Item
                        </button>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm transition">
                        Submit Return Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('returnForm', () => ({
                items: [{ id: Date.now(), product_id: '', quantity: 1, reason: '' }],
                addItem() {
                    this.items.push({ id: Date.now(), product_id: '', quantity: 1, reason: '' });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    } else {
                        alert('You must return at least one item.');
                    }
                }
            }));
        });
    </script>
</x-app-layout>
