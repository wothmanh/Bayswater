<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Course') }}: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Course Details Form --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Course Details</h3>
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

                    <form method="POST" action="{{ route('admin.courses.update', $course) }}">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Course Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $course->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- School Selection --}}
                            <div>
                                <x-input-label for="school_id" :value="__('School')" />
                                <select name="school_id" id="school_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select School --</option>
                                    @foreach($schools as $id => $name)
                                        <option value="{{ $id }}" {{ old('school_id', $course->school_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                                        <option value="{{ $id }}" {{ old('course_type_id', $course->course_type_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
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
                                        <option value="{{ $key }}" {{ old('pricing_type', $course->pricing_type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('pricing_type')" class="mt-2" />
                            </div>

                            {{-- Study Mode --}}
                            <div>
                                <x-input-label for="study_mode" :value="__('Study Mode (e.g., Full-time)')" />
                                <x-text-input id="study_mode" class="block mt-1 w-full" type="text" name="study_mode" :value="old('study_mode', $course->study_mode)" />
                                <x-input-error :messages="$errors->get('study_mode')" class="mt-2" />
                            </div>

                            {{-- Lessons Per Week --}}
                            <div>
                                <x-input-label for="lessons_per_week" :value="__('Lessons Per Week')" />
                                <x-text-input id="lessons_per_week" class="block mt-1 w-full" type="number" step="1" min="0" name="lessons_per_week" :value="old('lessons_per_week', $course->lessons_per_week)" />
                                <x-input-error :messages="$errors->get('lessons_per_week')" class="mt-2" />
                            </div>

                            {{-- Hours Per Week --}}
                            <div>
                                <x-input-label for="hours_per_week" :value="__('Hours Per Week')" />
                                <x-text-input id="hours_per_week" class="block mt-1 w-full" type="number" step="0.1" min="0" name="hours_per_week" :value="old('hours_per_week', $course->hours_per_week)" />
                                <x-input-error :messages="$errors->get('hours_per_week')" class="mt-2" />
                            </div>

                            {{-- Description --}}
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $course->description) }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                            {{-- Notes --}}
                            <div class="md:col-span-2">
                                <x-input-label for="notes" :value="__('Internal Notes (Optional)')" />
                                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes', $course->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>

                            {{-- Course Details Links Section --}}
                            <div class="md:col-span-2 mt-4 border-t pt-4 border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Course Details Links</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Add external links for this course (e.g. syllabus, brochure).</p>
                            </div>

                            <div class="md:col-span-2" x-data="{
                                detailLinks: {{ json_encode($course->detailLinks->map(fn($l) => ['url' => $l->url, 'button_text' => $l->button_text])) }} || []
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

                        </div> {{-- End Grid --}}

                        {{-- Active Checkbox --}}
                        <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', $course->active) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                            </label>
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.courses.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Update Course') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Course Prices Section (if pricing_type is 'per_week' and not a junior course) --}}
            @if($course->pricing_type == 'per_week' && $course->category !== 'junior')
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Weekly Prices</h3>
                        {{-- Use correct route name --}}
                        <a href="{{ route('admin.courses.prices.create', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            {{ __('Add Price Tier') }}
                        </a>
                    </div>
                    @include('admin.course_prices._index_table', ['coursePrices' => $course->coursePrices]) {{-- Assuming a partial view --}}
                </div>
            </div>
            @endif

            {{-- Course Schedules Section (if pricing_type is 'fixed_schedule') --}}
             @if($course->pricing_type == 'fixed_schedule')
             <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                 <div class="p-6 text-gray-900 dark:text-gray-100">
                     <div class="flex justify-between items-center mb-4">
                         <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Fixed Schedules</h3>
                          {{-- Use correct route name --}}
                         <a href="{{ route('admin.courses.schedules.create', $course) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                             {{ __('Add Schedule') }}
                         </a>
                     </div>
                     @include('admin.course_schedules._index_table', ['courseSchedules' => $course->courseSchedules]) {{-- Assuming a partial view --}}
                 </div>
             </div>
             @endif

        </div>
    </div>
</x-app-layout>
