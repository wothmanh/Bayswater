<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Price Tier for Accommodation') }}: {{ $accommodation->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Validation Error!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Use correct shallow route name --}}
                    <form method="POST" action="{{ route('admin.accommodation-prices.update', $accommodation_price) }}">
                        @csrf
                        @method('PUT')

                        {{-- Min Weeks --}}
                        <div class="mb-4">
                            <x-input-label for="min_weeks" :value="__('Minimum Weeks')" />
                            <x-text-input id="min_weeks" class="block mt-1 w-full" type="number" name="min_weeks" :value="old('min_weeks', $accommodation_price->min_weeks)" required min="1" />
                            <x-input-error :messages="$errors->get('min_weeks')" class="mt-2" />
                        </div>

                        {{-- Max Weeks --}}
                        <div class="mb-4">
                            <x-input-label for="max_weeks" :value="__('Maximum Weeks')" />
                            <x-text-input id="max_weeks" class="block mt-1 w-full" type="number" name="max_weeks" :value="old('max_weeks', $accommodation_price->max_weeks)" required min="1" />
                            <x-input-error :messages="$errors->get('max_weeks')" class="mt-2" />
                        </div>

                        {{-- Price Per Week (2025) --}}
                        <div class="mb-4">
                            <x-input-label for="price_per_week" :value="__('Price Per Week (2025)')" />
                            <x-text-input id="price_per_week" class="block mt-1 w-full" type="number" step="0.01" name="price_per_week" :value="old('price_per_week', $accommodation_price->price_per_week)" required min="0" />
                            <x-input-error :messages="$errors->get('price_per_week')" class="mt-2" />
                        </div>

                        {{-- Price Per Week (2026) --}}
                        <div class="mb-4">
                            <x-input-label for="price_per_week_2026" :value="__('Price Per Week (2026)')" />
                            <x-text-input id="price_per_week_2026" class="block mt-1 w-full" type="number" step="0.01" name="price_per_week_2026" :value="old('price_per_week_2026', $accommodation_price->price_per_week_2026)" min="0" />
                            <x-input-error :messages="$errors->get('price_per_week_2026')" class="mt-2" />
                        </div>

                        {{-- Active Checkbox --}}
                        <div class="block mt-4">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', $accommodation_price->active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                            </label>
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                             {{-- Link back to the accommodation edit page --}}
                             <a href="{{ route('admin.accommodations.edit', $accommodation) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Update Price Tier') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
