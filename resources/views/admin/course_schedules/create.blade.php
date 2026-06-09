<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Fixed Schedule for Course') }}: {{ $course->name }}
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

                    {{-- Use correct route name --}}
                    <form method="POST" action="{{ route('admin.courses.schedules.store', $course) }}">
                        @csrf

                        {{-- Start Date --}}
                        <div class="mb-4">
                            <x-input-label for="start_date" :value="__('Start Date')" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        {{-- Duration Weeks --}}
                        <div class="mb-4">
                            <x-input-label for="duration_weeks" :value="__('Duration (Weeks)')" />
                            <x-text-input id="duration_weeks" class="block mt-1 w-full" type="number" name="duration_weeks" :value="old('duration_weeks')" required min="1" />
                            <x-input-error :messages="$errors->get('duration_weeks')" class="mt-2" />
                        </div>

                        {{-- Fixed Price --}}
                        <div class="mb-4">
                            <x-input-label for="fixed_price" :value="__('Fixed Price')" />
                            <x-text-input id="fixed_price" class="block mt-1 w-full" type="number" step="0.01" name="fixed_price" :value="old('fixed_price')" required min="0" />
                            <x-input-error :messages="$errors->get('fixed_price')" class="mt-2" />
                        </div>

                        {{-- Active Checkbox --}}
                        <div class="block mt-4">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                            </label>
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                             {{-- Link back to the course edit page --}}
                             <a href="{{ route('admin.courses.edit', $course) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Save Schedule') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
