<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Exchange Name') }}: {{ $exchangeName->name }}
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

                    <form method="POST" action="{{ route('admin.exchange-names.update', $exchangeName) }}">
                        @csrf
                        @method('PUT')

                        {{-- Code --}}
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Code (ISO 4217)')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $exchangeName->name)" required maxlength="10" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Label --}}
                        <div class="mb-4">
                            <x-input-label for="label" :value="__('Display Label (Optional)')" />
                            <x-text-input id="label" class="block mt-1 w-full" type="text" name="label" :value="old('label', $exchangeName->label)" maxlength="100" />
                            <x-input-error :messages="$errors->get('label')" class="mt-2" />
                        </div>

                        <div class="border-t pt-6 mt-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Visible Regions</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Select the regions where this currency should be visible.
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($regions as $region)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="region_{{ $region->id }}" name="regions[]" type="checkbox" value="{{ $region->id }}"
                                                @checked($exchangeName->regions->contains($region->id))
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="region_{{ $region->id }}" class="font-medium text-gray-700 dark:text-gray-300">{{ $region->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.exchange-names.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Update Exchange Name') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>