<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Promo Code:') }} {{ $promo->code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('promos.update', $promo) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <x-input-label for="code" value="Promo Code" />
                                <x-text-input id="code" class="block mt-1 w-full uppercase" type="text" name="code" :value="old('code', $promo->code)" required autofocus />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="type" value="Discount Type" />
                                <select id="type" name="type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="percent" {{ old('type', $promo->type) === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="fixed" {{ old('type', $promo->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount (Rs.)</option>
                                </select>
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="value" value="Discount Value" />
                                <x-text-input id="value" class="block mt-1 w-full" type="number" step="0.01" name="value" :value="old('value', $promo->value)" required />
                                <x-input-error :messages="$errors->get('value')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="min_spend" value="Minimum Spend (Optional)" />
                                <x-text-input id="min_spend" class="block mt-1 w-full" type="number" step="0.01" name="min_spend" :value="old('min_spend', $promo->min_spend)" />
                                <x-input-error :messages="$errors->get('min_spend')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="max_uses" value="Maximum Uses (Optional)" />
                                <x-text-input id="max_uses" class="block mt-1 w-full" type="number" name="max_uses" :value="old('max_uses', $promo->max_uses)" placeholder="Leave blank for unlimited" />
                                <x-input-error :messages="$errors->get('max_uses')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="expires_at" value="Expiry Date (Optional)" />
                                <x-text-input id="expires_at" class="block mt-1 w-full" type="datetime-local" name="expires_at" :value="old('expires_at', $promo->expires_at ? $promo->expires_at->format('Y-m-d\TH:i') : '')" />
                                <x-input-error :messages="$errors->get('expires_at')" class="mt-2" />
                            </div>

                            <div class="col-span-1 md:col-span-2">
                                <label for="is_active" class="inline-flex items-center">
                                    <input id="is_active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="is_active" value="1" {{ old('is_active', $promo->is_active) ? 'checked' : '' }}>
                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Promo is Active</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>Update Promo</x-primary-button>
                            <a href="{{ route('promos.index') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
