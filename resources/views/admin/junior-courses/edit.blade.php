<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Junior Course') }}: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">{{ __('Validation Error') }}</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.junior-courses.update', $course->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Course Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $course->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="school_id" :value="__('School')" />
                                <select name="school_id" id="school_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">{{ __('-- Select School --') }}</option>
                                    @foreach($schools as $id => $name)
                                        <option value="{{ $id }}" {{ old('school_id', $course->school_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="course_type_id" :value="__('Course Type')" />
                                <select name="course_type_id" id="course_type_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">{{ __('-- Select Course Type --') }}</option>
                                    @foreach($courseTypes as $id => $name)
                                        <option value="{{ $id }}" {{ old('course_type_id', $course->course_type_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('course_type_id')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="pricing_type" :value="__('Pricing Type')" />
                                <select name="pricing_type" id="pricing_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">{{ __('-- Select Pricing Type --') }}</option>
                                    @foreach($pricingTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('pricing_type', $course->pricing_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pricing_type')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="study_mode" :value="__('Study Mode (e.g., Full-time)')" />
                                <x-text-input id="study_mode" class="block mt-1 w-full" type="text" name="study_mode" :value="old('study_mode', $course->study_mode)" />
                                <x-input-error :messages="$errors->get('study_mode')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="lessons_per_week" :value="__('Lessons Per Week')" />
                                <x-text-input id="lessons_per_week" class="block mt-1 w-full" type="number" step="1" min="0" name="lessons_per_week" :value="old('lessons_per_week', $course->lessons_per_week)" />
                                <x-input-error :messages="$errors->get('lessons_per_week')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="hours_per_week" :value="__('Hours Per Week')" />
                                <x-text-input id="hours_per_week" class="block mt-1 w-full" type="number" step="0.1" min="0" name="hours_per_week" :value="old('hours_per_week', $course->hours_per_week)" />
                                <x-input-error :messages="$errors->get('hours_per_week')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $course->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Internal Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes', $course->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </div>

                        <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                                {{ __('Junior Settings') }}
                            </h3>

                            @php
                                $settings = $course->juniorSettings;
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="start_date" :value="__('Start Date (Restriction)')" />
                                    <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', optional($settings?->start_date)->format('Y-m-d'))" />
                                    <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="end_date" :value="__('End Date (Restriction)')" />
                                    <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', optional($settings?->end_date)->format('Y-m-d'))" />
                                    <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="min_age" :value="__('Minimum Age')" />
                                    <x-text-input id="min_age" class="block mt-1 w-full" type="number" min="0" name="min_age" :value="old('min_age', $settings?->min_age)" />
                                    <x-input-error :messages="$errors->get('min_age')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="max_age" :value="__('Maximum Age')" />
                                    <x-text-input id="max_age" class="block mt-1 w-full" type="number" min="0" name="max_age" :value="old('max_age', $settings?->max_age)" />
                                    <x-input-error :messages="$errors->get('max_age')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="min_weeks" :value="__('Minimum Weeks')" />
                                    <x-text-input id="min_weeks" class="block mt-1 w-full" type="number" min="1" name="min_weeks" :value="old('min_weeks', $settings?->min_weeks)" />
                                    <x-input-error :messages="$errors->get('min_weeks')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="max_weeks" :value="__('Maximum Weeks')" />
                                    <x-text-input id="max_weeks" class="block mt-1 w-full" type="number" min="1" name="max_weeks" :value="old('max_weeks', $settings?->max_weeks)" />
                                    <x-input-error :messages="$errors->get('max_weeks')" class="mt-2" />
                                </div>
                            </div>

                            <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                                    {{ __('Included in this Programme') }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    {{ __('Checked items will appear in the quote as Included (£0).') }}
                                </p>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                        <thead class="bg-gray-50 dark:bg-gray-800">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    {{ __('Included?') }}
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                                    {{ __('Fee Item') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach([
                                                'includes_registration_fee' => 'Registration Fee',
                                                'includes_books_fee' => 'Books & Study Materials',
                                                'includes_accommodation' => 'Accommodation',
                                                'includes_accommodation_placement' => 'Accommodation Placement Fee',
                                                'includes_activities' => 'Activities',
                                                'includes_local_travel' => 'Local Travel',
                                                'includes_airport_transfer' => 'Airport Transfer',
                                                'includes_insurance' => 'Insurance'
                                            ] as $field => $label)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap w-10">
                                                     <input id="{{ $field }}" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="{{ $field }}" value="1" {{ old($field, $settings?->$field) ? 'checked' : '' }}>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    <label for="{{ $field }}">{{ __($label) }}</label>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>



                        <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                                {{ __('Allowed Accommodations for this Junior Course') }}
                            </h3>

                            <select name="accommodations[]" id="accommodations" multiple class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @foreach($accommodations as $accommodation)
                                    <option value="{{ $accommodation->id }}" {{ in_array($accommodation->id, old('accommodations', $selectedAccommodations)) ? 'selected' : '' }}>
                                        {{ $accommodation->school->name ?? '' }} - {{ $accommodation->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('accommodations')" class="mt-2" />
                        </div>

                        <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                                {{ __('Course Details Links') }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ __('Add external links for this course (e.g. syllabus, brochure).') }}</p>

                            <div x-data="{
                                detailLinks: {{ json_encode($course->juniorDetailLinks->map(fn($l) => ['url' => $l->url, 'button_text' => $l->button_text])) }} || []
                            }">
                                <template x-for="(link, index) in detailLinks" :key="index">
                                    <div class="flex gap-4 mb-4 items-start p-4 border border-gray-200 dark:border-gray-700 rounded-md bg-gray-50 dark:bg-gray-700/50">
                                        <div class="w-1/3">
                                            <x-input-label :value="__('Button Text')" />
                                            <x-text-input x-bind:name="'detail_links[' + index + '][button_text]'" x-model="link.button_text" class="block mt-1 w-full" type="text" placeholder="e.g. Course Details" required />
                                        </div>
                                        <div class="w-2/3">
                                            <x-input-label :value="__('URL')" />
                                            <x-text-input x-bind:name="'detail_links[' + index + '][url]'" x-model="link.url" class="block mt-1 w-full" type="url" placeholder="https://..." required />
                                        </div>
                                        <div class="mt-7">
                                            <button type="button" @click="detailLinks.splice(index, 1)" class="text-red-600 hover:text-red-900" title="Remove Link">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <button type="button" @click="detailLinks.push({url: '', button_text: ''})" class="mt-2 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                    {{ __('Add Link') }}
                                </button>
                            </div>
                        </div>

                        <div class="block mt-6">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', $course->active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                            </label>
                            <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.junior-courses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Update Junior Course') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if($course->pricing_type == 'per_week')
                    <div class="mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                            {{ __('Weekly Package Prices') }}
                        </h3>

                        <div class="flex justify-between items-center mb-4">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                {{ __('Manage junior package weekly price tiers for this course.') }}
                            </p>
                            <a href="{{ route('admin.junior-courses.prices.create', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                {{ __('Add Price Tier') }}
                            </a>
                        </div>

                        @include('admin.course_prices._index_table', ['coursePrices' => $course->coursePrices])
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
