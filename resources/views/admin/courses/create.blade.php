<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Course') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                    <form method="POST" action="{{ route('admin.courses.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Course Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- School Selection --}}
                            <div>
                                <x-input-label for="school_id" :value="__('School')" />
                                <select name="school_id" id="school_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select School --</option>
                                    @foreach($schools as $id => $name)
                                        <option value="{{ $id }}" {{ old('school_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                            </div>

                            {{-- Course Type Selection --}}
                            <div>
                                <x-input-label for="course_type_id" :value="__('Course Type')" />
                                <select name="course_type_id" id="course_type_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select Course Type --</option>
                                    @foreach($courseTypes as $id => $name)
                                        <option value="{{ $id }}" {{ old('course_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('course_type_id')" class="mt-2" />
                            </div>

                            {{-- Pricing Type --}}
                            <div>
                                <x-input-label for="pricing_type" :value="__('Pricing Type')" />
                                <select name="pricing_type" id="pricing_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select Pricing Type --</option>
                                    @foreach($pricingTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('pricing_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pricing_type')" class="mt-2" />
                            </div>

                            {{-- Study Mode --}}
                            <div>
                                <x-input-label for="study_mode" :value="__('Study Mode (e.g., Full-time)')" />
                                <x-text-input id="study_mode" class="block mt-1 w-full" type="text" name="study_mode" :value="old('study_mode')" />
                                <x-input-error :messages="$errors->get('study_mode')" class="mt-2" />
                            </div>

                            {{-- Lessons Per Week --}}
                            <div>
                                <x-input-label for="lessons_per_week" :value="__('Lessons Per Week')" />
                                <x-text-input id="lessons_per_week" class="block mt-1 w-full" type="number" step="1" min="0" name="lessons_per_week" :value="old('lessons_per_week')" />
                                <x-input-error :messages="$errors->get('lessons_per_week')" class="mt-2" />
                            </div>

                            {{-- Hours Per Week --}}
                            <div>
                                <x-input-label for="hours_per_week" :value="__('Hours Per Week')" />
                                <x-text-input id="hours_per_week" class="block mt-1 w-full" type="number" step="0.1" min="0" name="hours_per_week" :value="old('hours_per_week')" />
                                <x-input-error :messages="$errors->get('hours_per_week')" class="mt-2" />
                            </div>

                            {{-- Description --}}
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            {{-- Notes --}}
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Internal Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                        </div> {{-- End Grid --}}

                        {{-- Active Checkbox --}}
                        <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                            </label>
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.courses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Save Course') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
