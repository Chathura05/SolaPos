<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">📥 Import Stock (Excel/CSV)</h2>
            <a href="{{ route('inventory.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">← Back to Inventory</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if(session('error'))
                <div class="mb-4 px-4 py-3 bg-red-100 border border-red-400 text-red-800 rounded-lg">{{ session('error') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Instructions</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    To bulk update stock for a specific showroom, upload an Excel or CSV file. The file must contain exactly two columns with headers:
                    <strong class="text-gray-800 dark:text-gray-200">barcode</strong> and <strong class="text-gray-800 dark:text-gray-200">quantity</strong>.
                </p>
                <div class="p-4 bg-yellow-50 dark:bg-yellow-900/30 rounded-lg border border-yellow-200 dark:border-yellow-800 mb-4">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        <strong>Note:</strong> Uploading this file will create a <a href="{{ route('dispatches.index') }}" class="underline">Pending Dispatch</a>. The stock will <strong>not</strong> be instantly updated. The cashier at the selected showroom must log in and <strong>Accept</strong> the dispatch to finalize the stock addition.
                    </p>
                </div>
                <a href="{{ route('inventory.import.template') }}" class="inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Download Template
                </a>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
                <form action="{{ route('inventory.import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Showroom <span class="text-red-500">*</span></label>
                        <select name="showroom_id" required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">-- Select Showroom --</option>
                            @foreach($showrooms as $s)
                                <option value="{{ $s->id }}" {{ old('showroom_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('showroom_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excel/CSV File <span class="text-red-500">*</span></label>
                        <input type="file" name="file" accept=".csv, .xlsx" required
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300">
                        @error('file') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="{{ route('inventory.index') }}"
                           class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Cancel</a>
                        <button type="submit"
                                class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            🚀 Import File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
