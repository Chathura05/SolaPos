<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="px-4 py-3 bg-green-100 border border-green-400 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl overflow-hidden">
                <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Store Information --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">Store Information</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Store Name</label>
                                <input type="text" name="store_name" value="{{ setting('store_name', 'My POS System') }}" 
                                       class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Store Address</label>
                                <textarea name="store_address" rows="3" 
                                          class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ setting('store_address', '') }}</textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Contact Number</label>
                                <input type="text" name="store_phone" value="{{ setting('store_phone', '') }}" 
                                       class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Logo</label>
                                @if(setting('company_logo'))
                                    <div class="mt-2 mb-3">
                                        <img src="{{ Storage::url(setting('company_logo')) }}" alt="Company Logo" class="h-16 w-auto object-contain bg-gray-50 dark:bg-gray-700 p-1 rounded border dark:border-gray-600">
                                        <div class="mt-1 flex items-center">
                                            <input type="checkbox" id="remove_logo" name="remove_logo" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <label for="remove_logo" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remove logo</label>
                                        </div>
                                    </div>
                                @endif
                                <input type="file" name="company_logo" accept="image/png, image/jpeg, image/webp, image/svg+xml"
                                       class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-full file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-indigo-50 file:text-indigo-700
                                              hover:file:bg-indigo-100
                                              dark:file:bg-gray-700 dark:file:text-indigo-400">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recommended size: 200x50px. Max size: 2MB.</p>
                                @error('company_logo')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Preferences --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">Preferences</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Currency Symbol</label>
                                <input type="text" name="currency_symbol" value="{{ setting('currency_symbol', 'Rs.') }}" 
                                       class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">E.g. $, €, {{ setting('currency_symbol', 'Rs.') }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Receipt Footer Message</label>
                                <textarea name="receipt_footer" rows="3" 
                                          class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ setting('receipt_footer', 'Thank you for your purchase!') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This message will be printed at the bottom of the POS receipt.</p>
                            </div>
                        </div>

                        {{-- Loyalty Program --}}
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">Loyalty Program</h3>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Amount Spent to Earn 1 Point</label>
                                <div class="flex items-center mt-1">
                                    <span class="px-3 border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 rounded-l-lg">{{ setting('currency_symbol', 'Rs.') }}</span>
                                    <input type="number" step="0.01" name="loyalty_earning_rate" value="{{ setting('loyalty_earning_rate', 100) }}" 
                                        class="block w-full rounded-r-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">E.g., Spend {{ setting('currency_symbol', 'Rs.') }} 100 to get 1 Point.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Discount Value per Point</label>
                                <div class="flex items-center mt-1">
                                    <span class="px-3 border border-r-0 border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-600 text-gray-500 dark:text-gray-300 rounded-l-lg">{{ setting('currency_symbol', 'Rs.') }}</span>
                                    <input type="number" step="0.01" name="loyalty_redemption_value" value="{{ setting('loyalty_redemption_value', 1) }}" 
                                        class="block w-full rounded-r-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">E.g., 1 Point = {{ setting('currency_symbol', 'Rs.') }} 1 discount.</p>
                            </div>
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700 font-medium transition">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</x-app-layout>
