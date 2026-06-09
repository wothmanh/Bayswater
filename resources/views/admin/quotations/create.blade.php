<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Fees Calculator') }}
        </h2>
    </x-slot>

    <div class="py-6">
        {{-- Two column layout with form on left and results on right --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                {{-- Left column: Form --}}
                <div class="w-full lg:w-2/3 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">

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

                    {{-- Standard form submission --}}
                    <form method="POST" action="{{ route($calculateRoute ?? 'admin.quotations.calculate') }}" id="calculator-form">
                        @csrf

                        {{-- Form Sections with Bayswater styling --}}
                        <div class="mb-6">
                            <h4 class="text-md font-semibold text-white bg-bayswater-blue p-2 rounded-t-md">Course options</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-b-md"> {{-- Add padding/border/bg here --}}
                                {{-- Region --}}
                                <div>
                                    <x-input-label for="region_id" :value="__('Region')" class="text-gray-700 font-medium"/> {{-- Removed (Optional) --}}
                                    <select name="region_id" id="region_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm" required> {{-- Added required --}}
                                        <option value="">-- Select Region --</option>
                                        @foreach($regions as $id => $name)
                                            <option value="{{ $id }}" {{ old('region_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('region_id')" class="mt-2" />
                                </div>
                                {{-- Student Date of Birth --}}
                                <div>
                                    <div class="flex justify-between items-center">
                                        <x-input-label for="client_birthday" :value="__('Student Date of Birth')" class="text-gray-700 font-medium"/>
                                        <div id="age-display" class="text-sm font-medium text-bayswater-blue"></div>
                                    </div>
                                    <x-text-input id="client_birthday" class="block mt-1 w-full bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue" type="date" name="client_birthday" :value="old('client_birthday')" />
                                    <div class="flex justify-between mt-1">
                                        <p class="text-xs text-gray-500">Default: 18 years old</p>
                                        <p class="text-xs text-gray-500">Required for U18 fees</p>
                                    </div>
                                    <x-input-error :messages="$errors->get('client_birthday')" class="mt-2" />
                                </div>
                                {{-- Country --}}
                                <div>
                                    <x-input-label for="country_id" :value="__('Country')" class="text-gray-700 font-medium"/>
                                    <select name="country_id" id="country_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled> {{-- Use light bg/dark text --}}
                                        <option value="">-- Select country --</option>
                                        @foreach($countries as $id => $name)
                                            <option value="{{ $id }}" {{ old('country_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('country_id')" class="mt-2" />
                                </div>
                                {{-- City --}}
                                <div>
                                    <x-input-label for="city_id" :value="__('City')" class="text-gray-700 font-medium"/>
                                    <select name="city_id" id="city_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled> {{-- Use light bg/dark text --}}
                                        <option value="">-- Select City --</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" data-country="{{ $city->country_id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('city_id')" class="mt-2" />
                                </div>
                                {{-- School/Centre --}}
                                <div>
                                    <x-input-label for="school_id" :value="__('School/Centre')" class="text-gray-700 font-medium"/>
                                    <select name="school_id" id="school_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled> {{-- Use light bg/dark text --}}
                                        <option value="">-- Select Centre --</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" data-city="{{ $school->city_id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>{{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    {{-- Social icons moved to breakdown --}}
                                    <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                                </div>
                                {{-- Course Type --}}
                                <div>
                                    <x-input-label for="course_type_id" :value="__('Course Type')" class="text-gray-700 font-medium"/>
                                    <select name="course_type_id" id="course_type_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled> {{-- Use light bg/dark text --}}
                                        <option value="">-- Select Course Type --</option>
                                         @foreach($courseTypes as $courseType)
                                            <option value="{{ $courseType->id }}" {{ old('course_type_id') == $courseType->id ? 'selected' : '' }}>{{ $courseType->name }}</option>
                                         @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('course_type_id')" class="mt-2" />
                                </div>
                                {{-- Course --}}
                                <div>
                                    <x-input-label for="course_id" :value="__('Course')" class="text-gray-700 font-medium"/>
            <select name="course_id" id="course_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled> {{-- Use light bg/dark text --}}
                                        <option value="">-- Select Course --</option>
                                         @foreach($courses as $course)
                <option value="{{ $course->id }}" data-school="{{ $course->school_id }}" data-course-type="{{ $course->course_type_id }}" data-pricing-type="{{ $course->pricing_type }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
                                         @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('course_id')" class="mt-2" />
                                </div>
                                {{-- Start Date --}}
                                <div>
                                    <x-input-label for="course_start_date" :value="__('Start Date (Mondays Only)')" class="text-gray-700 dark:text-gray-300"/>
                                    {{-- Add min attribute for current year and pattern/JS for Monday check --}}
                                    <x-text-input id="course_start_date" class="block mt-1 w-full dark:bg-white dark:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed" type="date" name="course_start_date" :value="old('course_start_date')" required min="{{ date('Y') }}-01-01" disabled />
                                    <x-input-error :messages="$errors->get('course_start_date')" class="mt-2" />
                                    <p id="start_date_error" class="text-xs text-red-600 dark:text-red-400 mt-1" style="display: none;">Start date must be a Monday.</p>
                                </div>
                                {{-- Number of Weeks --}}
                                <div>
                                    <x-input-label for="course_duration_weeks" :value="__('Course Duration (weeks)')" class="text-gray-700 font-medium"/>
                                    {{-- Change to select dropdown --}}
                                    <select id="course_duration_weeks" name="course_duration_weeks" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                        <option value="">-- Select Course Duration --</option>
                                        {{-- Options will be populated by JS --}}
                                    </select>
                                    <x-input-error :messages="$errors->get('course_duration_weeks')" class="mt-2" />
                                </div>
                                
                                {{-- Special Discount For Section (hidden by default; dynamically toggled) --}}
                                @if(isset($nationalityDiscounts) && $nationalityDiscounts->count() > 0)
                                <div class="md:col-span-2 mt-4" id="special-discount-section" style="display: none;">
                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Special Discount For:</h5>
                                    <div class="space-y-2">
                                        @foreach($nationalityDiscounts as $discount)
                                        <div class="flex items-center">
                                            <input type="checkbox" 
                                                   id="nationality_discount_{{ $discount->id }}" 
                                                   name="nationality_discounts[]" 
                                                   value="{{ $discount->id }}"
                                                   class="nationality-discount-checkbox h-4 w-4 text-bayswater-blue focus:ring-bayswater-blue border-gray-300 rounded"
                                                   data-combinable="{{ $discount->combinable ? 'true' : 'false' }}">
                                            <label for="nationality_discount_{{ $discount->id }}" class="ml-2 text-sm text-gray-700">
                                                {{ $discount->nationality_title }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                
                                {{-- Add Course Button --}}
                                <div class="md:col-span-2 mt-4">
                                    <button type="button" id="add-course-btn" class="inline-flex items-center px-4 py-2 bg-bayswater-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bayswater-blue-dark focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Add Course
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1">Select a start date for the first course to enable this option</p>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Second Course Options Section (Hidden by default) --}}
                        <div class="mb-6" id="second-course-section" style="display: none;">
                            <div class="flex items-center justify-between">
                                <h4 class="text-md font-semibold text-white bg-bayswater-blue p-2 rounded-t-md flex-grow">Second Course Options</h4>
                                <button type="button" id="remove-course-btn" class="bg-red-600 hover:bg-red-700 active:bg-red-800 text-white p-3 rounded-t-md ml-1 transition-all duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 group" title="Remove Second Course" aria-label="Remove Second Course">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-b-md">
                                {{-- Shared Data Notice --}}
                                <div class="md:col-span-2 mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                    <p class="text-sm text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <strong>Note:</strong> Region, Student Date of Birth, Country, City, and School/Centre are shared between both courses and cannot be changed separately.
                                    </p>
                                </div>
                                
                                {{-- City --}}
                                <div>
                                    <x-input-label for="second_city_id" :value="__('City')" class="text-gray-700 font-medium"/>
                                    <select name="second_city_id" id="second_city_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                        <option value="">-- Select City --</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" data-country="{{ $city->country_id }}">{{ $city->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('second_city_id')" class="mt-2" />
                                </div>
                                
                                {{-- School/Centre --}}
                                <div>
                                    <x-input-label for="second_school_id" :value="__('School/Centre')" class="text-gray-700 font-medium"/>
                                    <select name="second_school_id" id="second_school_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                        <option value="">-- Select Centre --</option>
                                        @foreach($schools as $school)
                                            <option value="{{ $school->id }}" data-city="{{ $school->city_id }}">{{ $school->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('second_school_id')" class="mt-2" />
                                </div>
                                
                                {{-- Course Type --}}
                                <div>
                                    <x-input-label for="second_course_type_id" :value="__('Course Type')" class="text-gray-700 font-medium"/>
                                    <select name="second_course_type_id" id="second_course_type_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                        <option value="">-- Select Course Type --</option>
                                        @foreach($courseTypes as $courseType)
                                            <option value="{{ $courseType->id }}">{{ $courseType->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('second_course_type_id')" class="mt-2" />
                                </div>
                                
                                {{-- Course --}}
                                <div>
                                    <x-input-label for="second_course_id" :value="__('Course')" class="text-gray-700 font-medium"/>
            <select name="second_course_id" id="second_course_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                        <option value="">-- Select Course --</option>
                                        @foreach($courses as $course)
                <option value="{{ $course->id }}" data-school="{{ $course->school_id }}" data-course-type="{{ $course->course_type_id }}" data-pricing-type="{{ $course->pricing_type }}">{{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('second_course_id')" class="mt-2" />
                                </div>
                                
                                {{-- Start Date --}}
                                <div>
                                    <x-input-label for="second_course_start_date" :value="__('Start Date (Mondays Only)')" class="text-gray-700 font-medium"/>
                                    <x-text-input id="second_course_start_date" class="block mt-1 w-full bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue disabled:opacity-50 disabled:cursor-not-allowed" type="date" name="second_course_start_date" required min="{{ date('Y') }}-01-01" disabled />
                                    <x-input-error :messages="$errors->get('second_course_start_date')" class="mt-2" />
                                    <p id="second_start_date_error" class="text-xs text-red-600 mt-1" style="display: none;">Start date must be a Monday.</p>
                                </div>
                                
                                {{-- Number of Weeks --}}
                                <div>
                                    <x-input-label for="second_course_duration_weeks" :value="__('Course Duration (weeks)')" class="text-gray-700 font-medium"/>
                                    <select id="second_course_duration_weeks" name="second_course_duration_weeks" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                        <option value="">-- Select Course Duration --</option>
                                        {{-- Options will be populated by JS --}}
                                    </select>
                                    <x-input-error :messages="$errors->get('second_course_duration_weeks')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                         {{-- Accommodation Options Section --}}
                         <div class="mb-6">
                              <h4 class="text-md font-semibold text-white bg-bayswater-blue p-2 rounded-t-md">Accommodation options</h4>
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-b-md"> {{-- Add padding/border/bg here --}}
                                  {{-- Accommodation Selection --}}
                                 <div>
                                     <x-input-label for="accommodation_id" :value="__('Accommodation (Optional)')" class="text-gray-700 font-medium"/>
                                     <select name="accommodation_id" id="accommodation_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled> {{-- Use light bg/dark text --}}
                                         <option value="">-- No Accommodation --</option>
                                          @foreach($accommodations as $accom)
                                             <option value="{{ $accom->id }}" 
                                                     data-school="{{ $accom->school_id }}" 
                                                     data-restricted-course-types="{{ $accom->restrictedCourseTypes->pluck('id')->implode(',') }}"
                                                     data-restricted-courses="{{ $accom->restrictedCourses->pluck('id')->implode(',') }}"
                                                     data-requires-christmas-supplement="{{ $accom->requires_christmas_supplement ? '1' : '0' }}"
                                                     data-private-bathroom-enabled="{{ $accom->private_bathroom_enabled ? '1' : '0' }}"
                                                     data-private-bathroom-fee="{{ $accom->private_bathroom_fee ?? 0 }}"
                                                     data-dietary-supplement-enabled="{{ $accom->dietary_supplement_enabled ? '1' : '0' }}"
                                                     data-dietary-supplement-fee="{{ $accom->dietary_supplement_fee ?? 0 }}"
                                                     data-private-bathroom-enabled-2025="{{ $accom->private_bathroom_enabled ? '1' : '0' }}"
                                                     data-private-bathroom-fee-2025="{{ $accom->private_bathroom_fee ?? 0 }}"
                                                     data-dietary-supplement-enabled-2025="{{ $accom->dietary_supplement_enabled ? '1' : '0' }}"
                                                     data-dietary-supplement-fee-2025="{{ $accom->dietary_supplement_fee ?? 0 }}"
                                                     data-private-bathroom-enabled-2026="{{ $accom->private_bathroom_enabled_2026 ? '1' : '0' }}"
                                                     data-private-bathroom-fee-2026="{{ $accom->private_bathroom_fee_2026 ?? 0 }}"
                                                     data-dietary-supplement-enabled-2026="{{ $accom->dietary_supplement_enabled_2026 ? '1' : '0' }}"
                                                     data-dietary-supplement-fee-2026="{{ $accom->dietary_supplement_fee_2026 ?? 0 }}"
                                                     {{ old('accommodation_id') == $accom->id ? 'selected' : '' }}>{{ $accom->name }}</option>
                                          @endforeach
                                     </select>
                                     <x-input-error :messages="$errors->get('accommodation_id')" class="mt-2" />
                                 </div>
                                 {{-- Accommodation Weeks --}}
                                 <div id="accommodation_duration_div" style="{{ old('accommodation_id') ? '' : 'display: none;' }}">
                                     <x-input-label for="accommodation_duration_weeks" :value="__('Accommodation Duration (weeks)')" class="text-gray-700 font-medium"/>
                                     <select id="accommodation_duration_weeks" name="accommodation_duration_weeks" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                         <option value="">-- Select Accommodation Duration --</option>
                                         {{-- Options will be populated by JS --}}
                                     </select>
                                     <x-input-error :messages="$errors->get('accommodation_duration_weeks')" class="mt-2" />
                                 </div>

                                 {{-- Private Bathroom Option --}}
                                 <div id="private_bathroom_div" class="md:col-span-2" style="display: none;">
                                     <label for="private_bathroom" class="flex items-center">
                                         <input type="checkbox" id="private_bathroom" name="private_bathroom" value="1" class="rounded border-gray-300 text-bayswater-orange focus:ring-bayswater-orange" {{ old('private_bathroom') ? 'checked' : '' }}>
                                         <span class="ms-2 text-sm text-gray-700 font-medium">Private Bathroom</span>
                                         <span id="private_bathroom_fee_display" class="ms-2 text-sm text-gray-500"></span>
                                     </label>
                                     <x-input-error :messages="$errors->get('private_bathroom')" class="mt-2" />
                                 </div>

                                 {{-- Dietary Supplement (Halal) Option --}}
                                 <div id="dietary_supplement_div" class="md:col-span-2" style="display: none;">
                                     <label for="dietary_supplement" class="flex items-center">
                                         <input type="checkbox" id="dietary_supplement" name="dietary_supplement" value="1" class="rounded border-gray-300 text-bayswater-orange focus:ring-bayswater-orange" {{ old('dietary_supplement') ? 'checked' : '' }}>
                                         <span class="ms-2 text-sm text-gray-700 font-medium">Dietary Supplement (Halal)</span>
                                         <span id="dietary_supplement_fee_display" class="ms-2 text-sm text-gray-500"></span>
                                     </label>
                                     <x-input-error :messages="$errors->get('dietary_supplement')" class="mt-2" />
                                 </div>

                                 {{-- Christmas Accommodation Option --}}
                                 <div id="christmas_accommodation_div" class="mt-4" style="display: none;">
                                     <x-input-label for="christmas_accommodation" :value="__('Accommodation During Christmas')" class="text-gray-700 font-medium"/>
                                     <select id="christmas_accommodation" name="christmas_accommodation" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm">
                                         <option value="no">No</option>
                                         <option value="yes" selected>Yes</option>
                                     </select>
                                     <p class="text-xs text-gray-500 mt-1" id="christmas_period_info"></p>

                                     {{-- Extra Weeks Dropdown --}}
                                     <div id="christmas_extra_weeks_div" class="mt-2" style="display: none;">
                                         <x-input-label for="christmas_extra_weeks" :value="__('Extra Accommodation Weeks During Christmas')" class="text-gray-700 font-medium text-sm"/>
                                         <select id="christmas_extra_weeks" name="christmas_extra_weeks" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm">
                                             {{-- Options populated by JS --}}
                                         </select>
                                     </div>
                                     <x-input-error :messages="$errors->get('christmas_accommodation')" class="mt-2" />
                                 </div>

                                 {{-- Courier Fee (Hidden by default, shown only for schools with courier_fee_enabled) --}}
                                 <div class="md:col-span-2" id="courier_fee_div" style="display: none;">
                                     <x-input-label for="courier_fee_option" :value="__('Courier fee (e.g., for I-20/Visa)')" class="text-gray-700 font-medium"/>
                                     <select name="courier_fee_option" id="courier_fee_option" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm"> {{-- Use light bg/dark text --}}
                                         <option value="no" {{ old('courier_fee_option', 'no') == 'no' ? 'selected' : '' }}>No</option>
                                         <option value="yes" {{ old('courier_fee_option') == 'yes' ? 'selected' : '' }}>Yes</option>
                                     </select>
                                 </div>
                                 
                                 {{-- Add Accommodation Button --}}
                                 <div class="md:col-span-2 mt-4">
                                     <button type="button" id="add-accommodation-btn" class="inline-flex items-center px-4 py-2 bg-bayswater-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bayswater-blue-dark focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150" disabled>
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                         </svg>
                                         Add Accommodation
                                     </button>
                                     <p class="text-xs text-gray-500 mt-1">Select an accommodation to enable this option</p>
                                 </div>
                             </div>
                         </div>

                         {{-- Second Accommodation Options Section (Hidden by default) --}}
                         <div class="mb-6" id="second-accommodation-section" style="display: none;">
                             <div class="flex items-center justify-between">
                                 <h4 class="text-md font-semibold text-white bg-bayswater-blue p-2 rounded-t-md flex-grow">Second Accommodation Options</h4>
                                 <button type="button" id="remove-accommodation-btn" class="bg-red-600 hover:bg-red-700 active:bg-red-800 text-white p-3 rounded-t-md ml-1 transition-all duration-200 ease-in-out shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 group" title="Remove Second Accommodation" aria-label="Remove Second Accommodation">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                         <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                     </svg>
                                 </button>
                             </div>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-b-md">
                                 {{-- Shared Data Notice --}}
                                 <div class="md:col-span-2 mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                     <p class="text-sm text-blue-800">
                                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                         </svg>
                                         <strong>Note:</strong> School selection is shared with the first accommodation and cannot be changed separately.
                                     </p>
                                 </div>

                                 {{-- Second Accommodation Selection --}}
                                 <div>
                                     <x-input-label for="second_accommodation_id" :value="__('Second Accommodation')" class="text-gray-700 font-medium"/>
                                     <select name="second_accommodation_id" id="second_accommodation_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                         <option value="">-- Select Accommodation --</option>
                                         @foreach($accommodations as $accom)
                                             <option value="{{ $accom->id }}" 
                                                     data-school="{{ $accom->school_id }}" 
                                                     data-restricted-course-types="{{ $accom->restrictedCourseTypes->pluck('id')->implode(',') }}"
                                                     data-restricted-courses="{{ $accom->restrictedCourses->pluck('id')->implode(',') }}"
                                                     data-requires-christmas-supplement="{{ $accom->requires_christmas_supplement ? '1' : '0' }}"
                                                     data-private-bathroom-enabled="{{ $accom->private_bathroom_enabled ? '1' : '0' }}"
                                                     data-private-bathroom-fee="{{ $accom->private_bathroom_fee ?? 0 }}"
                                                     data-dietary-supplement-enabled="{{ $accom->dietary_supplement_enabled ? '1' : '0' }}"
                                                     data-dietary-supplement-fee="{{ $accom->dietary_supplement_fee ?? 0 }}"
                                                     data-private-bathroom-enabled-2025="{{ $accom->private_bathroom_enabled ? '1' : '0' }}"
                                                     data-private-bathroom-fee-2025="{{ $accom->private_bathroom_fee ?? 0 }}"
                                                     data-dietary-supplement-enabled-2025="{{ $accom->dietary_supplement_enabled ? '1' : '0' }}"
                                                     data-dietary-supplement-fee-2025="{{ $accom->dietary_supplement_fee ?? 0 }}"
                                                     data-private-bathroom-enabled-2026="{{ $accom->private_bathroom_enabled_2026 ? '1' : '0' }}"
                                                     data-private-bathroom-fee-2026="{{ $accom->private_bathroom_fee_2026 ?? 0 }}"
                                                     data-dietary-supplement-enabled-2026="{{ $accom->dietary_supplement_enabled_2026 ? '1' : '0' }}"
                                                     data-dietary-supplement-fee-2026="{{ $accom->dietary_supplement_fee_2026 ?? 0 }}"
                                                     {{ old('second_accommodation_id') == $accom->id ? 'selected' : '' }}>{{ $accom->name }}</option>
                                         @endforeach
                                     </select>
                                     <x-input-error :messages="$errors->get('second_accommodation_id')" class="mt-2" />
                                 </div>

                                 {{-- Second Accommodation Duration --}}
                                 <div id="second_accommodation_duration_div">
                                     <x-input-label for="second_accommodation_duration_weeks" :value="__('Accommodation Duration (weeks)')" class="text-gray-700 font-medium"/>
                                     <select id="second_accommodation_duration_weeks" name="second_accommodation_duration_weeks" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled>
                                         <option value="">-- Select Accommodation Duration --</option>
                                         {{-- Options will be populated by JS --}}
                                     </select>
                                     <x-input-error :messages="$errors->get('second_accommodation_duration_weeks')" class="mt-2" />
                                 </div>

                                 {{-- Second Private Bathroom Option --}}
                                 <div id="second_private_bathroom_div" class="md:col-span-2" style="display: none;">
                                     <label for="second_private_bathroom" class="flex items-center">
                                         <input type="checkbox" id="second_private_bathroom" name="second_private_bathroom" value="1" class="rounded border-gray-300 text-bayswater-orange focus:ring-bayswater-orange" {{ old('second_private_bathroom') ? 'checked' : '' }}>
                                         <span class="ms-2 text-sm text-gray-700 font-medium">Private Bathroom</span>
                                         <span id="second_private_bathroom_fee_display" class="ms-2 text-sm text-gray-500"></span>
                                     </label>
                                     <x-input-error :messages="$errors->get('second_private_bathroom')" class="mt-2" />
                                 </div>

                                 {{-- Second Dietary Supplement (Halal) Option --}}
                                 <div id="second_dietary_supplement_div" class="md:col-span-2" style="display: none;">
                                     <label for="second_dietary_supplement" class="flex items-center">
                                         <input type="checkbox" id="second_dietary_supplement" name="second_dietary_supplement" value="1" class="rounded border-gray-300 text-bayswater-orange focus:ring-bayswater-orange" {{ old('second_dietary_supplement') ? 'checked' : '' }}>
                                         <span class="ms-2 text-sm text-gray-700 font-medium">Dietary Supplement (Halal)</span>
                                         <span id="second_dietary_supplement_fee_display" class="ms-2 text-sm text-gray-500"></span>
                                     </label>
                                     <x-input-error :messages="$errors->get('second_dietary_supplement')" class="mt-2" />
                                 </div>

                                 {{-- Second Christmas Accommodation Option --}}
                                 <div id="second_christmas_accommodation_div" class="mt-4" style="display: none;">
                                     <x-input-label for="second_christmas_accommodation" :value="__('Accommodation During Christmas')" class="text-gray-700 font-medium"/>
                                     <select id="second_christmas_accommodation" name="second_christmas_accommodation" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm">
                                         <option value="false">No</option>
                                         <option value="true" selected>Yes</option>
                                     </select>
                                     <p class="text-xs text-gray-500 mt-1" id="second_christmas_period_info"></p>

                                     {{-- Second Extra Weeks Dropdown --}}
                                     <div id="second_christmas_extra_weeks_div" class="mt-2" style="display: none;">
                                         <x-input-label for="second_christmas_extra_weeks" :value="__('Extra Accommodation Weeks During Christmas')" class="text-gray-700 font-medium text-sm"/>
                                         <select id="second_christmas_extra_weeks" name="second_christmas_extra_weeks" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm">
                                             {{-- Options populated by JS --}}
                                         </select>
                                     </div>
                                     <x-input-error :messages="$errors->get('second_christmas_accommodation')" class="mt-2" />
                                 </div>
                             </div>
                         </div>

                         {{-- Optional Extras Section --}}
                         <div class="mb-6">
                              <h4 class="text-md font-semibold text-white bg-bayswater-blue p-2 rounded-t-md">Optional extras</h4>
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-b-md"> {{-- Add padding/border/bg here --}}
                                   {{-- Insurance Checkbox --}}
                                   <div class="md:col-span-2 mb-4" id="insurance-section" style="display: none;">
                                        <label for="insurance_selected" class="flex items-center">
                                            <input type="checkbox" id="insurance_selected" name="insurance_selected" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-bayswater-orange focus:ring-bayswater-orange dark:focus:ring-offset-gray-800" {{ old('insurance_selected') ? 'checked' : '' }}>
                                            <span class="ms-2 text-sm text-gray-600 dark:text-gray-400" id="insurance-label">Insurance</span>
                                        </label>
                                        <x-input-error :messages="$errors->get('insurance_selected')" class="mt-2" />
                                   </div>

                                   {{-- Generic Addons Checkboxes --}}
                                   <div class="md:col-span-2">
                                        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Select Addons:</h3>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            @foreach($addons as $addon)
                                                <label for="addon_{{ $addon->id }}" class="flex items-center">
                                                    <input type="checkbox" id="addon_{{ $addon->id }}" name="selected_addons[{{ $addon->id }}]" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-bayswater-orange focus:ring-bayswater-orange dark:focus:ring-offset-gray-800" {{ old('selected_addons.'.$addon->id) ? 'checked' : '' }}> {{-- Use orange accent --}}
                                                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $addon->name }} ({{ $addon->price_type == 'per_week' ? 'per week' : 'one time' }})</span>
                                                </label>
                                            @endforeach
                                        </div>
                                        <x-input-error :messages="$errors->get('selected_addons')" class="mt-2" />
                                   </div>

                                   {{-- Airport Transfer Arrival --}}
                                   <div>
                                       <x-input-label for="arrival_transfer_airport_id" :value="__('Arrival Transfer (Optional)')" class="text-gray-700 font-medium"/>
                                       <select name="arrival_transfer_airport_id" id="arrival_transfer_airport_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                           <option value="">-- Not Required --</option>
                                           {{-- Options populated by JS --}}
                                       </select>
                                       <x-input-error :messages="$errors->get('arrival_transfer_airport_id')" class="mt-2" />
                                   </div>

                                   {{-- Airport Transfer Departure --}}
                                   <div>
                                       <x-input-label for="departure_transfer_airport_id" :value="__('Departure Transfer (Optional)')" class="text-gray-700 font-medium"/>
                                       <select name="departure_transfer_airport_id" id="departure_transfer_airport_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                           <option value="">-- Not Required --</option>
                                           {{-- Options populated by JS --}}
                                       </select>
                                       <x-input-error :messages="$errors->get('departure_transfer_airport_id')" class="mt-2" />
                                   </div>

                              </div>
                         </div>

                        {{-- Calculate button removed as calculations are now automatic --}}
                    </form>
                    </div>
                </div>

                {{-- Right column: Results --}}
                <div class="w-full lg:w-1/3" id="results-container">
                    @isset($costBreakdown)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        {{-- Your quote section --}}
                        <div class="bg-bayswater-blue text-white p-3">
                            <h3 class="font-semibold text-lg text-white">Your quote</h3>
                        </div>

                        <div class="p-4">
                            {{-- Display Errors --}}
                            @if (!empty($costBreakdown['errors']))
                                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                    <strong class="font-bold">Calculation Errors:</strong>
                                    <ul class="list-disc list-inside">
                                        @foreach ($costBreakdown['errors'] as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Course Type --}}
                            @if(isset($costBreakdown['course_type_name']) && $costBreakdown['course_type_name'])
                            <div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Course Type</h4>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">{{ $costBreakdown['course_type_name'] }}</span>
                                    <span class="text-sm text-gray-500">Selected</span>
                                </div>
                            </div>
                            @endif

                            {{-- Course --}}
                            <div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Course</h4>
                                @php
                                    $courseTuition = 0;
                                    $courseName = '';
                                    foreach ($costBreakdown['items'] as $item) {
                                        if ($item['category'] === 'tuition') {
                                            $courseTuition = $item['amount'];
                                            $courseName = $item['name'];
                                            break;
                                        }
                                    }
                                @endphp
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">{{ $courseName }}</span>
                                    <span class="font-semibold">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($courseTuition, 2) }}</span>
                                </div>
                                <div class="text-sm text-gray-600 mt-2">
                                    <p><strong>Start date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['course_start_date'])->format('d M Y') }}</p>
                                    <p><strong>End date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['course_end_date'])->format('d M Y') }}</p>
                                    <p><strong>Duration:</strong> {{ $costBreakdown['course_duration_weeks'] }} weeks</p>
                                    @if(isset($costBreakdown['quotation_extraction_date_formatted']))
                                        <p><strong>Quotation Date:</strong> {{ $costBreakdown['quotation_extraction_date_formatted'] }}</p>
                                    @endif

                                    {{-- Year-based Subtotals --}}
                                    @if(isset($costBreakdown['year_subtotals']) && (($costBreakdown['year_subtotals']['2025'] ?? 0) > 0 || ($costBreakdown['year_subtotals']['2026'] ?? 0) > 0))
                                        <div class="mt-3 p-3 bg-gray-50 border border-gray-200 rounded-md">
                                            <p class="text-sm font-medium text-gray-800 mb-2">
                                                <i class="fas fa-calendar-alt mr-1"></i>Year-based Breakdown
                                            </p>
                                            @if(($costBreakdown['year_subtotals']['2025'] ?? 0) > 0)
                                                <div class="flex justify-between items-center mb-1">
                                                    <span class="text-xs text-gray-600">2025 Subtotal:</span>
                                                    <span class="text-xs font-medium">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($costBreakdown['year_subtotals']['2025'], 2) }}</span>
                                                </div>
                                            @endif
                                            @if(($costBreakdown['year_subtotals']['2026'] ?? 0) > 0)
                                                <div class="flex justify-between items-center mb-1">
                                                    <span class="text-xs text-gray-600">2026 Subtotal:</span>
                                                    <span class="text-xs font-medium">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($costBreakdown['year_subtotals']['2026'], 2) }}</span>
                                                </div>
                                            @endif
                                            @if(isset($costBreakdown['pricing_rule_applied']))
                                                <div class="mt-2 pt-2 border-t border-gray-300">
                                                    <span class="text-xs text-blue-600 font-medium">{{ $costBreakdown['pricing_rule_applied'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Christmas Break Information --}}
                                    @if(isset($costBreakdown['christmas_break']) && $costBreakdown['christmas_break']['has_break'])
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-sm font-medium text-blue-800 mb-1">
                                                <i class="fas fa-snowflake mr-1"></i>Christmas Break Notice
                                            </p>
                                            <p class="text-xs text-blue-700">
                                                {{ $costBreakdown['christmas_break']['explanation'] }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Accommodation --}}
                            @php
                                $accommodationTotal = $costBreakdown['subtotals']['accommodation'] ?? 0;
                                $accommodationName = '';
                                foreach ($costBreakdown['items'] as $item) {
                                    if ($item['category'] === 'accommodation' && !str_contains($item['name'], 'Fee')) {
                                        $accommodationName = $item['name'];
                                        break;
                                    }
                                }
                            @endphp
                            @if($accommodationTotal > 0)
                            <div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Accommodation</h4>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">{{ $accommodationName }}</span>
                                    <span class="font-semibold">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($accommodationTotal, 2) }}</span>
                                </div>
                            </div>
                            @endif

                            {{-- Sub Total --}}
                            <div class="py-3 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Sub Total</span>
                                    <span class="font-semibold">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($costBreakdown['subtotals']['tuition'] + $costBreakdown['subtotals']['accommodation'], 2) }}</span>
                                </div>
                            </div>

                            {{-- Optional extras --}}
                            @php
                                $feesTotal = $costBreakdown['subtotals']['fees'] ?? 0;
                                $addonsTotal = $costBreakdown['subtotals']['addons'] ?? 0;
                            @endphp
                            @if($feesTotal > 0 || $addonsTotal > 0)
                            <div class="mt-6 mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Optional extras</h4>
                                @foreach ($costBreakdown['items'] as $item)
                                    @if($item['category'] === 'fees' || $item['category'] === 'addons')
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm">
                                            @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'web'])
                                        </span>
                                        <span class="font-semibold">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($item['amount'], 2) }}</span>
                                    </div>
                                    @endif
                                @endforeach
                            </div>

                            {{-- Sub Total --}}
                            <div class="py-3 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Sub Total</span>
                                    <span class="font-semibold">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($costBreakdown['subtotals']['fees'] + $costBreakdown['subtotals']['addons'], 2) }}</span>
                                </div>
                            </div>
                            @endif

                            {{-- Display Discounts --}}
                            @if (!empty($costBreakdown['discounts']))
                            <div class="mt-6 mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Discounts Applied</h4>
                                @foreach ($costBreakdown['discounts'] as $discount)
                                    <div class="flex justify-between items-center mb-1 text-green-600">
                                        <span class="text-sm">
                                            @if(!empty($discount['is_nationality']) || empty($discount['hidden']))
                                                {{ $discount['name'] }}
                                            @endif
                                        </span>
                                        <span class="font-semibold">-{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($discount['amount'], 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Notes --}}
                            @if (!empty($costBreakdown['notes']))
                            <div class="mt-6 mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Notes</h4>
                                <ul class="list-disc list-inside text-sm text-gray-600">
                                    @foreach ($costBreakdown['notes'] as $note)
                                        <li>{{ $note }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Total --}}
                            <div class="mt-6 py-4 bg-bayswater-blue text-white px-4 -mx-4">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-lg">Total:</span>
                                    <span class="font-bold text-lg">{{ $costBreakdown['currency_symbol'] ?? '' }}{{ number_format($costBreakdown['total'], 2) }}</span>
                                </div>
                            </div>

                            {{-- Quote Action Buttons --}}
                            <div class="mt-4 flex justify-end space-x-4 pb-4">
                                <button type="button" id="print-quote" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Print Quote
                                </button>
                                <button type="button" id="download-pdf" class="inline-flex items-center px-4 py-2 bg-bayswater-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bayswater-blue-dark focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download PDF
                                </button>
                            </div>

                            {{-- Hidden forms moved outside the @isset block --}}
                        </div>
                    </div>
                    @else
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="bg-bayswater-blue text-white p-3">
                            <h3 class="font-semibold text-lg text-white">Your quote</h3>
                        </div>
                        <div class="p-6 text-center text-gray-500">
                            <p>Fill out the form and click Calculate to see your quote.</p>
                        </div>
                    </div>
                    @endisset
                </div>
            </div>

            {{-- Hidden forms for PDF and Print actions --}}
            <form id="pdf-form" action="{{ route($pdfRoute ?? 'admin.quotations.pdf') }}" method="POST" style="display: none;">
                @csrf
                {{-- Form fields will be populated by JavaScript --}}
            </form>

            <form id="print-form" action="{{ route($printRoute ?? 'admin.quotations.print') }}" method="POST" target="_blank" style="display: none;">
                @csrf
                {{-- Form fields will be populated by JavaScript --}}
            </form>

    </div> {{-- Close div.py-6 --}}

    {{-- Basic JS for filtering dropdowns, toggling visibility, and date picker --}}
    @push('scripts')
    <script>
        // Custom ValidationError class to handle validation errors
        class ValidationError extends Error {
            constructor(message, errors) {
                super(message);
                this.name = 'ValidationError';
                this.errors = errors;
            }
        }

        // Store course prices passed from controller (already grouped)
const allCoursePrices = @json($allCoursePrices);
const allCourseSchedules = @json($allCourseSchedules);
const juniorCourseAccommodationMap = @json($juniorCourseAccommodationMap ?? []);
const juniorCourseSettings = @json($juniorCourseSettings ?? []);
            const courseDetailLinks = @json($courseDetailLinksMap ?? []);
            const juniorCourseDetailLinks = @json($juniorCourseDetailLinksMap ?? []);
            const courseDetailsButtonText = @json($courseDetailsButtonText ?? 'Course details');

// School Social Accounts (Preloaded)
const schoolSocials = @json($schools->mapWithKeys(function ($school) {
    return [$school->id => $school->socialAccounts->map(function ($account) {
        return [
            'platform' => $account->platform,
            'url' => $account->url
        ];
    })];
}));

        // Dynamic route configuration for agent vs admin access
        const routeConfig = {
            schoolDetails: '{{ route("schools.get-details", ["school" => "SCHOOL_ID"]) }}',
            schoolAirports: '{{ route("schools.get-airports", ["school" => "SCHOOL_ID"]) }}',
            schoolCourseTypes: '{{ route("schools.get-course-types", ["school" => "SCHOOL_ID"]) }}'
        };

        console.log('Script loaded');

        // Debug flag for Christmas settings - set to true to enable debugging
        const debugChristmasSettings = false; // Set to false for production

        // Global variables to store school-specific Christmas dates
        let schoolChristmasStartDate = null;
        let schoolChristmasEndDate = null;
        let extraAccommodationWeeks = 0; // Initialize, will be fetched via AJAX
        let currentSchoolAirports = []; // Store fetched airports for filtering

        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            console.log('DOM fully loaded');

            // DIAGNOSTIC: Inspect HTML structure when DOM is loaded
            setTimeout(function() {
                console.log('\n=== INITIAL HTML INSPECTION (DOM loaded) ===');
                inspectHtmlStructure();
            }, 500);

            // --- Get DOM Elements ---
            const countrySelect = document.getElementById('country_id');
            const citySelect = document.getElementById('city_id');
            const schoolSelect = document.getElementById('school_id'); // Centre
            const courseTypeSelect = document.getElementById('course_type_id');
            const courseSelect = document.getElementById('course_id');
            const accommodationSelect = document.getElementById('accommodation_id');
            const accommodationDurationDiv = document.getElementById('accommodation_duration_div');
            const accommodationDurationSelect = document.getElementById('accommodation_duration_weeks');
            const courseDurationSelect = document.getElementById('course_duration_weeks'); // Changed from Input to Select
            const startDateInput = document.getElementById('course_start_date');
            const christmasAccommodationDiv = document.getElementById('christmas_accommodation_div');
            const christmasAccommodationSelect = document.getElementById('christmas_accommodation');
            const christmasPeriodInfo = document.getElementById('christmas_period_info');
            const christmasExtraWeeksDiv = document.getElementById('christmas_extra_weeks_div');
            const christmasExtraWeeksSelect = document.getElementById('christmas_extra_weeks');
            const arrivalAirportSelect = document.getElementById('arrival_transfer_airport_id'); // New
            const departureAirportSelect = document.getElementById('departure_transfer_airport_id'); // New
            const calculatorForm = document.getElementById('calculator-form');
            const resultsContainer = document.getElementById('results-container');
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'text-center py-4';
            loadingIndicator.innerHTML = '<div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-bayswater-blue"></div><p class="mt-2 text-gray-600">Calculating...</p>';

            // --- Helper Functions ---

            // Function to format date as Month Day, Year
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                try {
                    const date = new Date(dateString + 'T00:00:00'); // Ensure correct parsing
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                } catch (e) {
                    console.error("Error formatting date:", dateString, e);
                    return 'Invalid Date';
                }
            }

            // Function to populate course type dropdowns
            function populateCourseTypes(courseTypes) {
                // Main Course Type
                const courseTypeSelect = document.getElementById('course_type_id');
                if (!courseTypeSelect) return;

                const currentVal = courseTypeSelect.value;
                
                courseTypeSelect.innerHTML = '<option value="">-- Select Course Type --</option>';
                
                courseTypes.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    courseTypeSelect.appendChild(option);
                });
                
                // Restore value if possible
                if (currentVal && courseTypes.some(t => String(t.id) === String(currentVal))) {
                    courseTypeSelect.value = currentVal;
                } else {
                    courseTypeSelect.value = "";
                }
                
                // Enable if we have options
                courseTypeSelect.disabled = courseTypes.length === 0;

                // Second Course Type (if exists)
                const secondCourseTypeSelect = document.getElementById('second_course_type_id');
                if (secondCourseTypeSelect) {
                    const currentSecondVal = secondCourseTypeSelect.value;
                    secondCourseTypeSelect.innerHTML = '<option value="">-- Select Course Type --</option>';
                    
                    courseTypes.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        secondCourseTypeSelect.appendChild(option);
                    });

                    if (currentSecondVal && courseTypes.some(t => String(t.id) === String(currentSecondVal))) {
                        secondCourseTypeSelect.value = currentSecondVal;
                    } else {
                        secondCourseTypeSelect.value = "";
                    }
                    
                    // Enable if we have options (and if second course section is active/visible, handled elsewhere)
                    // But we should at least not force disable it here unless empty
                    // secondCourseTypeSelect.disabled = courseTypes.length === 0; 
                }
            }

            // Function to populate Christmas extra weeks dropdown
            function populateChristmasExtraWeeks(maxWeeks) {
                if (!christmasExtraWeeksSelect || !christmasExtraWeeksDiv) return;

                if (debugChristmasSettings) {
                    console.log('\n--- Populating Christmas Extra Weeks ---');
                    console.log('Max weeks:', maxWeeks);
                }
                maxWeeks = parseInt(maxWeeks) || 0;
                if (maxWeeks <= 0) {
                    christmasExtraWeeksSelect.innerHTML = '<option value="0">0 weeks</option>'; // Add a 0 option if none available
                    christmasExtraWeeksDiv.style.display = 'none'; // Hide if 0
                    if (debugChristmasSettings) console.log('No extra weeks available, hiding dropdown.');
                    return;
                }

                // Clear existing options
                christmasExtraWeeksSelect.innerHTML = '';

                // Add options from 1 to maxWeeks
                for (let i = 1; i <= maxWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} ${i === 1 ? 'week' : 'weeks'}`;
                    christmasExtraWeeksSelect.appendChild(option);
                }

                // Select the maximum value by default
                if (christmasExtraWeeksSelect.options.length > 0) {
                    christmasExtraWeeksSelect.selectedIndex = maxWeeks - 1; // Select last option (max weeks)
                }

                // Show/hide based on Christmas Accommodation selection
                if (christmasAccommodationSelect) {
                    christmasExtraWeeksDiv.style.display = (christmasAccommodationSelect.value === 'yes') ? 'block' : 'none';
                }

                if (debugChristmasSettings) {
                    console.log('Christmas extra weeks dropdown populated with', christmasExtraWeeksSelect.options.length, 'options');
                    console.log('Extra weeks div display:', christmasExtraWeeksDiv.style.display);
                }
            }

            // Function to populate second Christmas extra weeks dropdown
            function populateSecondChristmasExtraWeeks(maxWeeks) {
                const secondChristmasExtraWeeksSelect = document.getElementById('second_christmas_extra_weeks');
                const secondChristmasExtraWeeksDiv = document.getElementById('second_christmas_extra_weeks_div');
                const secondChristmasAccommodationSelect = document.getElementById('second_christmas_accommodation');
                
                if (!secondChristmasExtraWeeksSelect || !secondChristmasExtraWeeksDiv) return;

                if (debugChristmasSettings) {
                    console.log('\n--- Populating Second Christmas Extra Weeks ---');
                    console.log('Max weeks:', maxWeeks);
                }
                maxWeeks = parseInt(maxWeeks) || 0;
                if (maxWeeks <= 0) {
                    secondChristmasExtraWeeksSelect.innerHTML = '<option value="0">0 weeks</option>'; // Add a 0 option if none available
                    secondChristmasExtraWeeksDiv.style.display = 'none'; // Hide if 0
                    if (debugChristmasSettings) console.log('No extra weeks available for second accommodation, hiding dropdown.');
                    return;
                }

                // Clear existing options
                secondChristmasExtraWeeksSelect.innerHTML = '';

                // Add options from 1 to maxWeeks
                for (let i = 1; i <= maxWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} ${i === 1 ? 'week' : 'weeks'}`;
                    secondChristmasExtraWeeksSelect.appendChild(option);
                }

                // Select the maximum value by default
                if (secondChristmasExtraWeeksSelect.options.length > 0) {
                    secondChristmasExtraWeeksSelect.selectedIndex = maxWeeks - 1; // Select last option (max weeks)
                }

                // Show/hide based on Christmas Accommodation selection
                if (secondChristmasAccommodationSelect) {
                    secondChristmasExtraWeeksDiv.style.display = (secondChristmasAccommodationSelect.value === 'true') ? 'block' : 'none';
                }

                if (debugChristmasSettings) {
                    console.log('Second Christmas extra weeks dropdown populated with', secondChristmasExtraWeeksSelect.options.length, 'options');
                    console.log('Second extra weeks div display:', secondChristmasExtraWeeksDiv.style.display);
                }
            }


            // Function to check if accommodation period overlaps with Christmas period
            function checkChristmasOverlap() {
                const startDateStr = startDateInput.value;
                const accomWeeks = parseInt(accommodationDurationSelect.value) || 0;

                if (!startDateStr || accomWeeks <= 0 || !schoolChristmasStartDate || !schoolChristmasEndDate) {
                    if (debugChristmasSettings) console.log('Christmas overlap check: Missing data (start date, accom weeks, or school Christmas dates)');
                    return false; // Cannot check without necessary data
                }

                try {
                    const startDate = new Date(startDateStr + 'T00:00:00');
                    
                    // Calculate accommodation end date using Monday-to-Friday logic (matching backend)
                    let accommodationStart = new Date(startDate);
                    // Ensure the start date is a Monday
                    if (accommodationStart.getDay() !== 1) { // 1 = Monday
                        // If not Monday, move to the next Monday
                        const daysUntilMonday = (8 - accommodationStart.getDay()) % 7;
                        accommodationStart.setDate(accommodationStart.getDate() + daysUntilMonday);
                    }
                    
                    // Calculate end date: add (weeks - 1) weeks, then go to Friday of that week
                    const accomEndDate = new Date(accommodationStart);
                    accomEndDate.setDate(accomEndDate.getDate() + ((accomWeeks - 1) * 7)); // Go to start of final week
                    // Go to Friday of that week (Friday = 5, so add days to get to Friday)
                    const dayOfWeek = accomEndDate.getDay();
                    const daysToFriday = (5 - dayOfWeek + 7) % 7;
                    accomEndDate.setDate(accomEndDate.getDate() + daysToFriday);

                    const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                    const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');

                    // Check for overlap: (StartA <= EndB) and (EndA >= StartB)
                    const overlaps = (accommodationStart <= christmasEnd && accomEndDate >= christmasStart);

                    if (debugChristmasSettings) {
                        console.log('Christmas overlap check:', {
                            courseStart: startDate.toISOString().split('T')[0],
                            accomStart: accommodationStart.toISOString().split('T')[0],
                            accomEnd: accomEndDate.toISOString().split('T')[0],
                            christmasStart: christmasStart.toISOString().split('T')[0],
                            christmasEnd: christmasEnd.toISOString().split('T')[0],
                            overlaps: overlaps
                        });
                    }
                    return overlaps;

                } catch (e) {
                    console.error("Error calculating Christmas overlap:", e);
                    return false;
                }
            }

            // Function to check if second accommodation period overlaps with Christmas period
            function checkSecondChristmasOverlap() {
                const startDateInput = document.getElementById('course_start_date');
                const accommodationDurationSelect = document.getElementById('accommodation_duration_weeks');
                const courseDurationSelect = document.getElementById('course_duration_weeks');
                const secondAccommodationDurationSelect = document.getElementById('second_accommodation_duration_weeks');
                const secondCourseDurationSelect = document.getElementById('second_course_duration_weeks');

                const startDateStr = startDateInput ? startDateInput.value : '';
                const secondAccomWeeks = parseInt(secondAccommodationDurationSelect ? secondAccommodationDurationSelect.value : '') || 0;
                const firstAccomWeeks = parseInt(accommodationDurationSelect ? accommodationDurationSelect.value : '') || 0;

                if (!startDateStr || secondAccomWeeks <= 0 || !schoolChristmasStartDate || !schoolChristmasEndDate) {
                    if (debugChristmasSettings) console.log('Second Christmas overlap check: Missing data (start date, accom weeks, or school Christmas dates)');
                    return false; // Cannot check without necessary data
                }

                try {
                    const courseStartDate = new Date(startDateStr + 'T00:00:00');
                    
                    // Get combined course duration (first + second course if selected)
                    const firstCourseDuration = parseInt(courseDurationSelect ? courseDurationSelect.value : '') || 0;
                    const secondCourseDuration = parseInt(secondCourseDurationSelect ? secondCourseDurationSelect.value : '') || 0;
                    const totalCourseDuration = firstCourseDuration + secondCourseDuration;
                    
                    if (debugChristmasSettings) {
                        console.log('Second Christmas overlap - Course durations:', {
                            firstCourse: firstCourseDuration,
                            secondCourse: secondCourseDuration,
                            totalCombined: totalCourseDuration
                        });
                    }
                    
                    // Calculate when second accommodation starts (matching backend logic)
                    let secondAccommodationStart;
                    
                    if (firstAccomWeeks > 0) {
                        // Second accommodation starts on Monday after first accommodation ends
                        // First, calculate first accommodation end date
                        let firstAccommodationStart = new Date(courseStartDate);
                        if (firstAccommodationStart.getDay() !== 1) { // 1 = Monday
                            const daysUntilMonday = (8 - firstAccommodationStart.getDay()) % 7;
                            firstAccommodationStart.setDate(firstAccommodationStart.getDate() + daysUntilMonday);
                        }
                        
                        const firstAccommodationEnd = new Date(firstAccommodationStart);
                        firstAccommodationEnd.setDate(firstAccommodationEnd.getDate() + ((firstAccomWeeks - 1) * 7));
                        const dayOfWeek = firstAccommodationEnd.getDay();
                        const daysToFriday = (5 - dayOfWeek + 7) % 7;
                        firstAccommodationEnd.setDate(firstAccommodationEnd.getDate() + daysToFriday);
                        
                        // Second accommodation starts on Monday after first accommodation ends
                        secondAccommodationStart = new Date(firstAccommodationEnd);
                        secondAccommodationStart.setDate(secondAccommodationStart.getDate() + 1); // Day after first ends
                        // Move to next Monday
                        if (secondAccommodationStart.getDay() !== 1) {
                            const daysUntilMonday = (8 - secondAccommodationStart.getDay()) % 7;
                            secondAccommodationStart.setDate(secondAccommodationStart.getDate() + daysUntilMonday);
                        }
                    } else {
                        // If no first accommodation, second accommodation starts from course start
                        secondAccommodationStart = new Date(courseStartDate);
                        if (secondAccommodationStart.getDay() !== 1) {
                            const daysUntilMonday = (8 - secondAccommodationStart.getDay()) % 7;
                            secondAccommodationStart.setDate(secondAccommodationStart.getDate() + daysUntilMonday);
                        }
                    }
                    
                    // Calculate second accommodation end date
                    // CRITICAL FIX: When two courses are selected, the second accommodation should extend 
                    // to cover the combined course duration, not just its individual weeks
                    let secondAccommodationEnd;
                    
                    if (totalCourseDuration > 0 && secondCourseDuration > 0) {
                        // When two courses are selected, calculate end date based on combined course duration
                        // to match backend FeeCalculatorService logic
                        const courseEnd = new Date(courseStartDate);
                        courseEnd.setDate(courseEnd.getDate() + ((totalCourseDuration - 1) * 7));
                        
                        // Ensure course end is on a Friday (matching backend Monday-Friday logic)
                        const courseEndDayOfWeek = courseEnd.getDay();
                        const daysToFriday = (5 - courseEndDayOfWeek + 7) % 7;
                        courseEnd.setDate(courseEnd.getDate() + daysToFriday);
                        
                        // Second accommodation should extend to match the combined course end date
                        secondAccommodationEnd = new Date(courseEnd);
                        
                        if (debugChristmasSettings) {
                            console.log('Second accommodation end calculated from combined course duration:', {
                                totalCourseDuration: totalCourseDuration,
                                courseEndDate: courseEnd.toISOString().split('T')[0],
                                secondAccomEnd: secondAccommodationEnd.toISOString().split('T')[0]
                            });
                        }
                    } else {
                        // Fallback to individual accommodation weeks calculation
                        secondAccommodationEnd = new Date(secondAccommodationStart);
                        secondAccommodationEnd.setDate(secondAccommodationEnd.getDate() + ((secondAccomWeeks - 1) * 7));
                        const dayOfWeek = secondAccommodationEnd.getDay();
                        const daysToFriday = (5 - dayOfWeek + 7) % 7;
                        secondAccommodationEnd.setDate(secondAccommodationEnd.getDate() + daysToFriday);
                    }

                    const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                    const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');

                    // Check for overlap: (StartA <= EndB) and (EndA >= StartB)
                    const overlaps = (secondAccommodationStart <= christmasEnd && secondAccommodationEnd >= christmasStart);

                    if (debugChristmasSettings) {
                        console.log('Second Christmas overlap check:', {
                            courseStart: courseStartDate.toISOString().split('T')[0],
                            secondAccomStart: secondAccommodationStart.toISOString().split('T')[0],
                            secondAccomEnd: secondAccommodationEnd.toISOString().split('T')[0],
                            christmasStart: christmasStart.toISOString().split('T')[0],
                            christmasEnd: christmasEnd.toISOString().split('T')[0],
                            overlaps: overlaps
                        });
                    }
                    return overlaps;

                } catch (e) {
                    console.error("Error calculating second Christmas overlap:", e);
                    return false;
                }
            }

            // Function to update the visibility and content of the Christmas section
            function updateChristmasSectionVisibility() {
                if (!accommodationSelect.value) {
                    // Hide if no accommodation is selected
                    if (christmasAccommodationDiv) christmasAccommodationDiv.style.display = 'none';
                    if (debugChristmasSettings) console.log('Hiding Christmas section: No accommodation selected.');
                    return;
                }

                const overlaps = checkChristmasOverlap();

                if (overlaps) {
                    if (christmasAccommodationDiv) christmasAccommodationDiv.style.display = 'block';
                    if (christmasPeriodInfo) christmasPeriodInfo.textContent = `Christmas period: ${formatDate(schoolChristmasStartDate)} to ${formatDate(schoolChristmasEndDate)}`;
                    populateChristmasExtraWeeks(extraAccommodationWeeks); // Repopulate/show/hide extra weeks
                    if (debugChristmasSettings) console.log('Showing Christmas section. Overlap detected.');
                } else {
                    if (christmasAccommodationDiv) christmasAccommodationDiv.style.display = 'none';
                    // Reset selections when hidden? Optional, maybe keep selection if user toggles back quickly.
                    // christmasAccommodationSelect.value = 'no';
                    // christmasExtraWeeksDiv.style.display = 'none';
                    if (debugChristmasSettings) console.log('Hiding Christmas section: No overlap detected.');
                }
            }

            // Function to update the visibility and content of the second Christmas section
            function updateSecondChristmasSectionVisibility() {
                const secondAccommodationSelect = document.getElementById('second_accommodation_id');
                const secondChristmasAccommodationDiv = document.getElementById('second_christmas_accommodation_div');
                const secondChristmasPeriodInfo = document.getElementById('second_christmas_period_info');
                const secondChristmasExtraWeeksDiv = document.getElementById('second_christmas_extra_weeks_div');
                const secondChristmasExtraWeeksSelect = document.getElementById('second_christmas_extra_weeks');

                if (!secondAccommodationSelect || !secondAccommodationSelect.value) {
                    // Hide if no accommodation is selected
                    if (secondChristmasAccommodationDiv) secondChristmasAccommodationDiv.style.display = 'none';
                    if (debugChristmasSettings) console.log('Hiding second Christmas section: No accommodation selected.');
                    return;
                }

                const overlaps = checkSecondChristmasOverlap();

                if (overlaps) {
                    if (secondChristmasAccommodationDiv) secondChristmasAccommodationDiv.style.display = 'block';
                    if (secondChristmasPeriodInfo) secondChristmasPeriodInfo.textContent = `Christmas period: ${formatDate(schoolChristmasStartDate)} to ${formatDate(schoolChristmasEndDate)}`;
                    populateSecondChristmasExtraWeeks(extraAccommodationWeeks); // Repopulate/show/hide extra weeks
                    if (debugChristmasSettings) console.log('Showing second Christmas section. Overlap detected.');
                } else {
                    if (secondChristmasAccommodationDiv) secondChristmasAccommodationDiv.style.display = 'none';
                    // Reset selections when hidden? Optional, maybe keep selection if user toggles back quickly.
                    // secondChristmasAccommodationSelect.value = 'no';
                    // secondChristmasExtraWeeksDiv.style.display = 'none';
                    if (debugChristmasSettings) console.log('Hiding second Christmas section: No overlap detected.');
                }
            }

            // Function to update courier fee visibility based on school settings
            function updateCourierFeeVisibility(enabled) {
                const courierFeeDiv = document.getElementById('courier_fee_div');
                const courierFeeSelect = document.getElementById('courier_fee_option');

                if (enabled) {
                    if (courierFeeDiv) courierFeeDiv.style.display = 'block';
                    console.log('Showing courier fee option for selected school');
                } else {
                    if (courierFeeDiv) courierFeeDiv.style.display = 'none';
                    // Reset selection when hiding
                    if (courierFeeSelect) courierFeeSelect.value = 'no';
                    console.log('Hiding courier fee option');
                }
            }

            // Function to update insurance visibility based on school settings
            function updateInsuranceVisibility(schoolData) {
                const insuranceDiv = document.getElementById('insurance-section');
                const insuranceCheckbox = document.getElementById('insurance_selected');
                const insuranceLabel = document.getElementById('insurance-label');

                if (schoolData && (schoolData.insurance_fee_per_week > 0 || schoolData.insurance_fee_per_week_2026 > 0)) {
                    // Show insurance option
                    if (insuranceDiv) insuranceDiv.style.display = 'block';
                    
                    // Update label with pricing information
                    const fee2025 = parseFloat(schoolData.insurance_fee_per_week || 0).toFixed(2);
                    const fee2026 = parseFloat(schoolData.insurance_fee_per_week_2026 || 0).toFixed(2);
                    
                    let labelText = 'Insurance';
                    if (fee2025 > 0 && fee2026 > 0) {
                        labelText = `Insurance (£${fee2025}/week 2025, £${fee2026}/week 2026)`;
                    } else if (fee2025 > 0) {
                        labelText = `Insurance (£${fee2025}/week)`;
                    } else if (fee2026 > 0) {
                        labelText = `Insurance (£${fee2026}/week)`;
                    }
                    
                    if (insuranceLabel) insuranceLabel.textContent = labelText;
                    console.log('Showing insurance option for selected school:', labelText);
                } else {
                    // Hide insurance option
                    if (insuranceDiv) insuranceDiv.style.display = 'none';
                    // Reset selection when hiding
                    if (insuranceCheckbox) insuranceCheckbox.checked = false;
                    console.log('Hiding insurance option');
                }
            }

            // Make function globally accessible for AJAX calls
            window.updateInsuranceVisibility = updateInsuranceVisibility;

            // Function to get social icon SVG
            function getSocialIcon(platform) {
                const size = "w-5 h-5";
                const icons = {
                    instagram: `<svg class="${size}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772 4.902 4.902 0 011.772-1.153c.636-.247 1.363-.416 2.427-.465 1.067-.047 1.407-.06 3.808-.06h.63zm1.51 1.562c-2.332 0-2.68.01-3.65.054-.92.042-1.42.197-1.756.328-.445.174-.763.38-1.096.713-.334.333-.54.65-.713 1.096-.131.336-.286.836-.328 1.756-.044.97-.054 1.318-.054 3.65v.63c0 2.332.01 2.68.054 3.65.042.92.197 1.42.328 1.756.174.445.38.763.713 1.096.333.334.65.54 1.096.713.336.131.836.286 1.756.328.97.044 1.318.054 3.65.054h.63c2.332 0 2.68-.01 3.65-.054.92-.042 1.42-.197 1.756-.328.445-.174 763-.38 1.096-.713.334-.333.54-.65.713-1.096.131-.336.286-.836.328-1.756.044-.97.054-1.318.054-3.65v-.63c0-2.332-.01-2.68-.054-3.65-.042-.92-.197-1.42-.328-1.756-.174-.445-.38-.763-.713-1.096-.333-.334-.65-.54-1.096-.713-.336-.131-.836-.286-1.756-.328-.97-.044-1.318-.054-3.65-.054h-.63zm-5.028 3.522a3.522 3.522 0 110 7.044 3.522 3.522 0 010-7.044zM6.828 11.522a2.022 2.022 0 100 4.044 2.022 2.022 0 000-4.044zm9.328-5.322a1.022 1.022 0 110 2.044 1.022 1.022 0 010-2.044z" clip-rule="evenodd" /></svg>`,
                    facebook: `<svg class="${size}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" /></svg>`,
                    tiktok: `<svg class="${size}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93v6.16c0 2.58-1.24 5.23-4.38 5.65-3.21.41-6.42-1.61-6.6-4.89-.2-3.38 2.44-6.26 5.84-6.38v4.04c-1.24.18-2.18 1.44-1.8 2.67.24.8 1.05 1.34 1.9 1.33 1.1 0 2.04-1.02 2.04-2.12v-1.13c0-3.37 0-6.74 0-10.12 0-.91 0-1.82 0-2.73z"/></svg>`,
                    linkedin: `<svg class="${size}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd" /></svg>`,
                    youtube: `<svg class="${size}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.418 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.814.419-7.814.419s-6.255 0-7.814-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.254.418-4.814a2.507 2.507 0 0 1 1.768-1.768C5.744 5 11.998 5 11.998 5s6.255 0 7.814.418ZM15.194 12 10 15V9l5.194 3Z" clip-rule="evenodd" /></svg>`,
                    x: `<svg class="${size}" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M13.6823 10.6218L20.2391 3H18.6854L12.9921 9.61788L8.44486 3H3.2002L10.0765 13.0074L3.2002 21H4.75404L10.7663 14.0113L15.5685 21H20.8131L13.6819 10.6218H13.6823ZM11.5541 13.0956L10.8574 12.0991L5.31391 4.16971H7.70053L12.1742 10.5689L12.8709 11.5655L18.6861 19.8835H16.2995L11.5541 13.096V13.0956Z"/></svg>`,
                    website: `<svg class="${size}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S12 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S12 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>`
                };
                return icons[platform.toLowerCase()] || icons['website'];
            }

            function updateSchoolSocials(schoolId) {
                const container = document.getElementById('school-social-icons');
                if (!container) return;
                
                container.innerHTML = '';
                
                if (!schoolId || !schoolSocials[schoolId]) return;
                
                const socials = schoolSocials[schoolId];
                if (socials.length === 0) return;

                socials.forEach(account => {
                    const link = document.createElement('a');
                    link.href = account.url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.className = 'text-gray-500 hover:text-bayswater-blue transition-colors duration-200';
                    link.title = account.platform.charAt(0).toUpperCase() + account.platform.slice(1);
                    
                    // Add SVG icon based on platform
                    link.innerHTML = getSocialIcon(account.platform);
                    
                    container.appendChild(link);
                });
            }

            // Make function globally accessible
            window.updateSchoolSocials = updateSchoolSocials;
            
            // --- Filtering Functions --- (Updated for iOS Safari compatibility)
            function filterOptions(targetSelect, attributeName, filterValue) {
                console.log(`Filtering ${targetSelect.id} by ${attributeName}=${filterValue}`);

                // iOS Safari does not reliably honor display:none on <option>.
                // Cache original options and rebuild the list with only matching options.
                if (!targetSelect._allOptions) {
                    targetSelect._allOptions = Array.from(targetSelect.options).map(opt => opt.cloneNode(true));
                    const defaultOpt = targetSelect._allOptions.find(o => o.value === "");
                    targetSelect._defaultOption = defaultOpt ? defaultOpt.cloneNode(true) : (() => {
                        const o = document.createElement('option');
                        o.value = "";
                        o.textContent = targetSelect.options[0]?.textContent || '-- Select --';
                        return o;
                    })();
                }

                const prevValue = targetSelect.value;
                const allOptions = targetSelect._allOptions;
                const defaultOption = targetSelect._defaultOption.cloneNode(true);

                let filtered = [];
                const showAll = attributeName === '__ALL__' || filterValue === '__ALL__';
                if (showAll) {
                    // Special mode: show all options (except the default duplicate)
                    filtered = allOptions.filter(opt => opt.value !== "");
                } else if (filterValue !== null && filterValue !== undefined && filterValue !== '') {
                    if (Array.isArray(attributeName)) {
                        const filters = Array.isArray(filterValue) ? filterValue.map(v => String(v)) : [String(filterValue)];
                        filtered = allOptions.filter(opt => {
                            if (opt.value === "") return false; // skip default duplicates
                            return attributeName.every((attr, idx) => String(opt.getAttribute(attr)) === filters[idx]);
                        });
                    } else {
                        const strFilter = String(filterValue);
                        filtered = allOptions.filter(opt => {
                            if (opt.value === "") return false; // skip default duplicates
                            const attr = opt.getAttribute(attributeName);
                            return String(attr) === strFilter;
                        });
                    }
                } else {
                    // No filter value: keep only the default option to avoid showing unrelated items when disabled
                    filtered = [];
                }

                // Rebuild the select options list
                targetSelect.innerHTML = '';
                targetSelect.appendChild(defaultOption);

                filtered.forEach(opt => {
                    const clone = opt.cloneNode(true);
                    clone.hidden = false;
                    clone.disabled = false;
                    targetSelect.appendChild(clone);
                });

                // Restore selection if still present; otherwise reset to default
                const valueToSelect = filtered.some(opt => opt.value === prevValue) ? prevValue : "";
                const changed = targetSelect.value !== valueToSelect;
                targetSelect.value = valueToSelect;

                if (changed) {
                    targetSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    console.log(`  Changed selection to ${targetSelect.value}`);
                }

                console.log(`  ${filtered.length} options visible in ${targetSelect.id}`);
            }

            // Rebuild Course Type options to only those available for the selected school
            function rebuildCourseTypesForSchool(selectedSchoolId) {
                // Cache original course type options and default option
                if (!courseTypeSelect._allOptions) {
                    courseTypeSelect._allOptions = Array.from(courseTypeSelect.options).map(opt => opt.cloneNode(true));
                    const defaultOpt = courseTypeSelect._allOptions.find(o => o.value === "");
                    courseTypeSelect._defaultOption = defaultOpt ? defaultOpt.cloneNode(true) : (() => {
                        const o = document.createElement('option');
                        o.value = "";
                        o.textContent = courseTypeSelect.options[0]?.textContent || '-- Select Course Type --';
                        return o;
                    })();
                }

                // Ensure we have the full course options cached for filtering
                if (!courseSelect._allOptions) {
                    courseSelect._allOptions = Array.from(courseSelect.options).map(opt => opt.cloneNode(true));
                    const defaultCourseOpt = courseSelect._allOptions.find(o => o.value === "");
                    courseSelect._defaultOption = defaultCourseOpt ? defaultCourseOpt.cloneNode(true) : (() => {
                        const o = document.createElement('option');
                        o.value = "";
                        o.textContent = courseSelect.options[0]?.textContent || '-- Select Course --';
                        return o;
                    })();
                }

                const prevValue = courseTypeSelect.value;
                const allTypeOptions = courseTypeSelect._allOptions;
                const defaultTypeOption = courseTypeSelect._defaultOption.cloneNode(true);

                let allowedTypeIds = new Set();
                if (selectedSchoolId) {
                    // Determine which course types exist for the selected school by inspecting all courses
                    courseSelect._allOptions.forEach(opt => {
                        if (opt.value === "") return; // skip default
                        const schoolId = String(opt.getAttribute('data-school'));
                        if (schoolId === String(selectedSchoolId)) {
                            const typeId = String(opt.getAttribute('data-course-type'));
                            if (typeId) allowedTypeIds.add(typeId);
                        }
                    });
                }

                // Rebuild Course Type select to default-only or allowed types
                courseTypeSelect.innerHTML = '';
                courseTypeSelect.appendChild(defaultTypeOption);

                if (selectedSchoolId && allowedTypeIds.size > 0) {
                    allTypeOptions.forEach(opt => {
                        if (opt.value === "") return; // skip default duplicates
                        if (allowedTypeIds.has(String(opt.value))) {
                            const clone = opt.cloneNode(true);
                            clone.hidden = false;
                            clone.disabled = false;
                            courseTypeSelect.appendChild(clone);
                        }
                    });
                }

                // Reset selection if not present after rebuild
                const hasPrev = Array.from(courseTypeSelect.options).some(o => o.value === prevValue);
                courseTypeSelect.value = hasPrev ? prevValue : "";

                // Disable if we only have the default option
                courseTypeSelect.disabled = courseTypeSelect.options.length <= 1 || !selectedSchoolId;
            }

            function toggleAccommodationDuration() {
                const show = accommodationSelect && accommodationSelect.value !== "";
                if (accommodationDurationDiv) accommodationDurationDiv.style.display = show ? '' : 'none';
                if (accommodationDurationSelect) {
                    accommodationDurationSelect.required = show;
                    accommodationDurationSelect.disabled = !show || (accommodationSelect && accommodationSelect.disabled);
                }

                // Toggle accommodation options visibility
                toggleAccommodationOptions();

                if (show && !accommodationSelect.disabled) {
                    console.log('Showing accommodation duration dropdown');
                    // Populate accommodation weeks dropdown and get whether a value was selected
                    const valueWasSelected = populateAccommodationWeeks();
                    updateChristmasSectionVisibility(); // Check visibility when accommodation is shown/duration populated

                    // If we have a course and course duration selected, trigger calculation
                    // This ensures we maintain the calculation results when accommodation is selected
                    if (courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                        console.log('Course and duration already selected, triggering calculation');
                        // Use setTimeout to ensure the DOM is updated before calculation
                        setTimeout(autoCalculate, 50);
                    }
                } else {
                    // Reset dropdown
                    console.log('Hiding accommodation duration dropdown');
                    accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';
                    updateChristmasSectionVisibility(); // Hide Christmas section if accommodation is hidden

                    // If we have a course and course duration selected, trigger calculation
                    // This ensures we maintain the calculation results when accommodation is deselected
                    if (courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                        console.log('Course and duration already selected, triggering calculation');
                        // Use setTimeout to ensure the DOM is updated before calculation
                        setTimeout(autoCalculate, 50);
                    }
                }
            }

            // Function to toggle accommodation options visibility and populate fees
            function toggleAccommodationOptions() {
                console.log('toggleAccommodationOptions called');
                const selectedAccommodationId = accommodationSelect.value;
                console.log('Selected accommodation ID:', selectedAccommodationId);
                
                const privateBathroomDiv = document.getElementById('private_bathroom_div');
                const dietarySupplementDiv = document.getElementById('dietary_supplement_div');
                const privateBathroomCheckbox = document.getElementById('private_bathroom');
                const dietarySupplementCheckbox = document.getElementById('dietary_supplement');
                const privateBathroomFeeSpan = document.getElementById('private_bathroom_fee_display');
                const dietarySupplementFeeSpan = document.getElementById('dietary_supplement_fee_display');

                console.log('Elements found:', {
                    privateBathroomDiv: !!privateBathroomDiv,
                    dietarySupplementDiv: !!dietarySupplementDiv,
                    privateBathroomCheckbox: !!privateBathroomCheckbox,
                    dietarySupplementCheckbox: !!dietarySupplementCheckbox,
                    privateBathroomFeeSpan: !!privateBathroomFeeSpan,
                    dietarySupplementFeeSpan: !!dietarySupplementFeeSpan
                });

                if (selectedAccommodationId) {
                    // Get the selected accommodation option element
                    const selectedOption = accommodationSelect.querySelector(`option[value="${selectedAccommodationId}"]`);
                    console.log('Selected option found:', !!selectedOption);
                    
                    if (selectedOption) {
                        // Determine the year from the course start date
                        const startDateInput = document.getElementById('course_start_date');
                        const startYear = startDateInput.value ? new Date(startDateInput.value).getFullYear() : new Date().getFullYear();
                        const yearSuffix = startYear >= 2026 ? '2026' : '2025';
                        
                        console.log('Year detection:', { startDate: startDateInput.value, startYear, yearSuffix });
                        
                        // Get accommodation data attributes based on year
                        const privateBathroomEnabled = selectedOption.getAttribute(`data-private-bathroom-enabled-${yearSuffix}`) === '1';
                        const privateBathroomFee = selectedOption.getAttribute(`data-private-bathroom-fee-${yearSuffix}`);
                        const dietarySupplementEnabled = selectedOption.getAttribute(`data-dietary-supplement-enabled-${yearSuffix}`) === '1';
                        const dietarySupplementFee = selectedOption.getAttribute(`data-dietary-supplement-fee-${yearSuffix}`);

                        console.log('Accommodation data:', {
                            yearSuffix,
                            privateBathroomEnabled,
                            privateBathroomFee,
                            dietarySupplementEnabled,
                            dietarySupplementFee
                        });

                        // Show/hide private bathroom option
                        if (privateBathroomEnabled) {
                            console.log('Showing private bathroom option');
                            if (privateBathroomDiv) privateBathroomDiv.style.display = '';
                            if (privateBathroomFeeSpan) privateBathroomFeeSpan.textContent = `£${privateBathroomFee || 0}/week`;
                        } else {
                            console.log('Hiding private bathroom option');
                            if (privateBathroomDiv) privateBathroomDiv.style.display = 'none';
                            if (privateBathroomCheckbox) privateBathroomCheckbox.checked = false;
                        }

                        // Show/hide dietary supplement option
                        if (dietarySupplementEnabled) {
                            console.log('Showing dietary supplement option');
                            if (dietarySupplementDiv) dietarySupplementDiv.style.display = '';
                            if (dietarySupplementFeeSpan) dietarySupplementFeeSpan.textContent = `£${dietarySupplementFee || 0}/week`;
                        } else {
                            console.log('Hiding dietary supplement option');
                            if (dietarySupplementDiv) dietarySupplementDiv.style.display = 'none';
                            if (dietarySupplementCheckbox) dietarySupplementCheckbox.checked = false;
                        }
                    }
                } else {
                    console.log('No accommodation selected, hiding both options');
                    // Hide both options when no accommodation is selected
                    if (privateBathroomDiv) privateBathroomDiv.style.display = 'none';
                    if (dietarySupplementDiv) dietarySupplementDiv.style.display = 'none';
                    if (privateBathroomCheckbox) privateBathroomCheckbox.checked = false;
                    if (dietarySupplementCheckbox) dietarySupplementCheckbox.checked = false;
                }
            }

            // Function to update accommodation add-ons based on year-specific settings
            function updateAccommodationAddOns() {
                console.log('updateAccommodationAddOns called - refreshing add-ons based on current year');
                // Call the existing toggleAccommodationOptions function which has the correct logic
                toggleAccommodationOptions();
            }

            // iOS: Forward 'input' to 'change' for selects so WebKit triggers handlers
            function forwardInputToChange(el) {
                if (!el) return;
                el.addEventListener('input', function() {
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
            // Attach forwarding to key selects (guards handle missing elements)
            [
                document.getElementById('country_id'),
                document.getElementById('city_id'),
                document.getElementById('school_id'),
                document.getElementById('course_type_id'),
                document.getElementById('course_id'),
                document.getElementById('course_start_date'),
                document.getElementById('course_duration_weeks'),
                document.getElementById('accommodation_id'),
                document.getElementById('accommodation_duration_weeks'),
                document.getElementById('second_city_id'),
                document.getElementById('second_school_id'),
                document.getElementById('second_course_type_id'),
                document.getElementById('second_course_id'),
                document.getElementById('second_course_start_date'),
                document.getElementById('second_course_duration_weeks'),
                document.getElementById('second_accommodation_id'),
                document.getElementById('second_accommodation_duration_weeks')
            ].forEach(forwardInputToChange);

            // --- Event Listeners ---
            // When country changes, filter cities
            countrySelect.addEventListener('change', function() {
                console.log('Country changed to: ' + this.value);
                // Reset all dependent dropdowns
                citySelect.value = '';
                schoolSelect.value = '';
                courseSelect.value = '';
                startDateInput.value = '';
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';
                accommodationSelect.value = '';
                accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';
                populateAirportDropdowns([]); // Clear airport dropdowns
                arrivalAirportSelect.disabled = true; // Disable airport dropdowns
                departureAirportSelect.disabled = true;

                // Hide accommodation duration and Christmas section
                toggleAccommodationDuration(); // This will also call updateChristmasSectionVisibility

                // Reset course duration
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';

                // Enable/disable city dropdown based on country selection
                if (this.value) {
                    // Enable city dropdown
                    citySelect.disabled = false;

                    // iOS-compatible filtering: rebuild city options
                    filterOptions(citySelect, 'data-country', this.value);
                } else {
                    // Disable city dropdown if no country selected
                    citySelect.disabled = true;

                    // iOS-compatible clearing: rebuild to default only
                    filterOptions(citySelect, 'data-country', null);
                }

                // Disable all subsequent dropdowns
                schoolSelect.disabled = true;
                courseSelect.disabled = true;
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // arrivalAirportSelect.disabled = true; // Already disabled above
                // departureAirportSelect.disabled = true;

                // Clear other dropdowns via rebuild for iOS
                filterOptions(schoolSelect, 'data-city', null);
                filterOptions(courseSelect, 'data-school', null);
                filterOptions(accommodationSelect, 'data-school', null);
            });

            // When city changes, filter schools
            citySelect.addEventListener('change', function() {
                console.log('City changed to: ' + this.value);
                // Reset dependent dropdowns
                schoolSelect.value = '';
                courseSelect.value = '';
                startDateInput.value = '';
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';
                accommodationSelect.value = '';
                accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';
                populateAirportDropdowns([]); // Clear airport dropdowns
                arrivalAirportSelect.disabled = true; // Disable airport dropdowns
                departureAirportSelect.disabled = true;

                // Hide accommodation duration and Christmas section
                toggleAccommodationDuration(); // This will also call updateChristmasSectionVisibility

                // Reset course duration
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';

                // Enable/disable school dropdown based on city selection
                if (this.value) {
                    // Enable school dropdown
                    schoolSelect.disabled = false;

                    // iOS-compatible filtering: rebuild school options
                    filterOptions(schoolSelect, 'data-city', this.value);
                } else {
                    // Disable school dropdown if no city selected
                    schoolSelect.disabled = true;

                    // iOS-compatible clearing: rebuild to default only
                    filterOptions(schoolSelect, 'data-city', null);
                }

                // Disable all subsequent dropdowns
                courseSelect.disabled = true;
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // arrivalAirportSelect.disabled = true; // Already disabled above
                // departureAirportSelect.disabled = true;

                // Clear other dropdowns via rebuild for iOS
                filterOptions(courseSelect, 'data-school', null);
                filterOptions(accommodationSelect, 'data-school', null);
            });

            function filterAccommodations() {
                const schoolId = schoolSelect.value;
                const courseId = courseSelect.value;
                const courseTypeId = courseTypeSelect.value;
                const map = typeof juniorCourseAccommodationMap !== 'undefined' ? juniorCourseAccommodationMap : {};
                const juniorAllowed = map && courseId && Object.prototype.hasOwnProperty.call(map, courseId)
                    ? (map[courseId] || [])
                    : null;

                if (!accommodationSelect) {
                    return;
                }

                if (!accommodationSelect._allOptions) {
                    accommodationSelect._allOptions = Array.from(accommodationSelect.options).map(opt => opt.cloneNode(true));
                    const defaultOpt = accommodationSelect._allOptions.find(o => o.value === "");
                    accommodationSelect._defaultOption = defaultOpt ? defaultOpt.cloneNode(true) : (() => {
                        const o = document.createElement('option');
                        o.value = "";
                        o.textContent = accommodationSelect.options[0]?.textContent || '-- No Accommodation --';
                        return o;
                    })();
                }

                const allOptions = accommodationSelect._allOptions;
                const defaultOption = accommodationSelect._defaultOption.cloneNode(true);

                let filtered = [];

                if (!schoolId) {
                    filtered = [];
                } else {
                    filtered = allOptions.filter(opt => {
                        if (opt.value === "") return false;
                        
                        // 1. School Check
                        if (String(opt.getAttribute('data-school')) !== String(schoolId)) return false;
                        
                        // 2. Junior Map Check (Legacy/Override)
                        if (juniorAllowed && juniorAllowed.length > 0) {
                             const allowedSet = new Set(juniorAllowed.map(id => String(id)));
                             if (!allowedSet.has(String(opt.value))) return false;
                        }
                        
                        // 3. New Restrictions Check
                        const restrictedTypesStr = opt.getAttribute('data-restricted-course-types');
                        const restrictedCoursesStr = opt.getAttribute('data-restricted-courses');
                        
                        const restrictedTypes = restrictedTypesStr ? restrictedTypesStr.split(',').filter(Boolean) : [];
                        const restrictedCourses = restrictedCoursesStr ? restrictedCoursesStr.split(',').filter(Boolean) : [];
                        
                        if (restrictedCourses.length > 0) {
                            // If specific courses are restricted, MUST match courseId
                            if (!courseId || !restrictedCourses.includes(String(courseId))) {
                                return false;
                            }
                        } else if (restrictedTypes.length > 0) {
                            // If course types are restricted (and no course restriction), MUST match courseTypeId
                            if (!courseTypeId || !restrictedTypes.includes(String(courseTypeId))) {
                                return false;
                            }
                        }
                        
                        return true;
                    });
                }

                const previousValue = accommodationSelect.value;
                accommodationSelect.innerHTML = '';
                accommodationSelect.appendChild(defaultOption);
                filtered.forEach(opt => {
                    const clone = opt.cloneNode(true);
                    clone.hidden = false;
                    clone.disabled = false;
                    accommodationSelect.appendChild(clone);
                });

                const shouldKeepPrevious = filtered.some(opt => opt.value === previousValue);
                const newValue = shouldKeepPrevious ? previousValue : "";
                const changed = accommodationSelect.value !== newValue;
                accommodationSelect.value = newValue;

                if (changed) {
                    accommodationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            function filterSecondAccommodations() {
                const schoolId = schoolSelect.value;
                
                let courseId = null;
                let courseTypeId = null;
                
                // Determine governing course (Second Course if active, otherwise First Course)
                if (typeof isSecondCourseVisible !== 'undefined' && isSecondCourseVisible && secondCourseSelect && secondCourseSelect.value) {
                    courseId = secondCourseSelect.value;
                    courseTypeId = secondCourseTypeSelect.value;
                } else {
                    courseId = courseSelect.value;
                    courseTypeId = courseTypeSelect.value;
                }

                const map = typeof juniorCourseAccommodationMap !== 'undefined' ? juniorCourseAccommodationMap : {};
                const juniorAllowed = map && courseId && Object.prototype.hasOwnProperty.call(map, courseId)
                    ? (map[courseId] || [])
                    : null;

                if (!secondAccommodationSelect) {
                    return;
                }

                if (!secondAccommodationSelect._allOptions) {
                    secondAccommodationSelect._allOptions = Array.from(secondAccommodationSelect.options).map(opt => opt.cloneNode(true));
                    const defaultOpt = secondAccommodationSelect._allOptions.find(o => o.value === "");
                    secondAccommodationSelect._defaultOption = defaultOpt ? defaultOpt.cloneNode(true) : (() => {
                        const o = document.createElement('option');
                        o.value = "";
                        o.textContent = secondAccommodationSelect.options[0]?.textContent || '-- Select Accommodation --';
                        return o;
                    })();
                }

                const allOptions = secondAccommodationSelect._allOptions;
                const defaultOption = secondAccommodationSelect._defaultOption.cloneNode(true);

                let filtered = [];

                if (!schoolId) {
                    filtered = [];
                } else {
                    filtered = allOptions.filter(opt => {
                        if (opt.value === "") return false;
                        
                        // 1. School Check
                        if (String(opt.getAttribute('data-school')) !== String(schoolId)) return false;
                        
                        // 2. Junior Map Check (Legacy/Override)
                        if (juniorAllowed && juniorAllowed.length > 0) {
                             const allowedSet = new Set(juniorAllowed.map(id => String(id)));
                             if (!allowedSet.has(String(opt.value))) return false;
                        }
                        
                        // 3. New Restrictions Check
                        const restrictedTypesStr = opt.getAttribute('data-restricted-course-types');
                        const restrictedCoursesStr = opt.getAttribute('data-restricted-courses');
                        
                        const restrictedTypes = restrictedTypesStr ? restrictedTypesStr.split(',').filter(Boolean) : [];
                        const restrictedCourses = restrictedCoursesStr ? restrictedCoursesStr.split(',').filter(Boolean) : [];
                        
                        if (restrictedCourses.length > 0) {
                            // If specific courses are restricted, MUST match courseId
                            if (!courseId || !restrictedCourses.includes(String(courseId))) {
                                return false;
                            }
                        } else if (restrictedTypes.length > 0) {
                            // If course types are restricted (and no course restriction), MUST match courseTypeId
                            if (!courseTypeId || !restrictedTypes.includes(String(courseTypeId))) {
                                return false;
                            }
                        }
                        
                        return true;
                    });
                }

                const previousValue = secondAccommodationSelect.value;
                secondAccommodationSelect.innerHTML = '';
                secondAccommodationSelect.appendChild(defaultOption);
                filtered.forEach(opt => {
                    const clone = opt.cloneNode(true);
                    clone.hidden = false;
                    clone.disabled = false;
                    secondAccommodationSelect.appendChild(clone);
                });

                const shouldKeepPrevious = filtered.some(opt => opt.value === previousValue);
                const newValue = shouldKeepPrevious ? previousValue : "";
                const changed = secondAccommodationSelect.value !== newValue;
                secondAccommodationSelect.value = newValue;

                if (changed) {
                    secondAccommodationSelect.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }

            function applyJuniorCourseDateLimits(courseId) {
                const settings = juniorCourseSettings && Object.prototype.hasOwnProperty.call(juniorCourseSettings, courseId)
                    ? juniorCourseSettings[courseId]
                    : null;

                if (!settings || !startDatePicker) {
                    return;
                }

                if (settings.start_date) {
                    startDatePicker.set('minDate', settings.start_date);
                }

                if (settings.end_date) {
                    startDatePicker.set('maxDate', settings.end_date);
                }

                if (startDateInput.value) {
                    const current = new Date(startDateInput.value + 'T00:00:00');
                    if (settings.start_date) {
                        const min = new Date(settings.start_date + 'T00:00:00');
                        if (current < min) {
                            startDateInput.value = '';
                        }
                    }
                    if (settings.end_date) {
                        const max = new Date(settings.end_date + 'T00:00:00');
                        if (current > max) {
                            startDateInput.value = '';
                        }
                    }
                }
            }

            // When school changes, filter courses and accommodations, fetch details & airports
            schoolSelect.addEventListener('change', function() {
                console.log('School changed to: ' + this.value);
                const selectedSchoolId = this.value;

                // Update Social Icons
                updateSchoolSocials(selectedSchoolId);

                // Reset dependent dropdowns
                courseTypeSelect.value = '';
                courseSelect.value = '';
                startDateInput.value = '';
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';
                accommodationSelect.value = '';
                accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';
                populateAirportDropdowns([]); // Clear airport dropdowns
                arrivalAirportSelect.disabled = true; // Disable airport dropdowns initially
                departureAirportSelect.disabled = true;

                // Reset school-specific details
                extraAccommodationWeeks = 0;
                schoolChristmasStartDate = null;
                schoolChristmasEndDate = null;

                // Hide accommodation duration and Christmas section
                toggleAccommodationDuration(); // This will also call updateChristmasSectionVisibility

                // Hide courier fee when no school is selected or school changes
                updateCourierFeeVisibility(false);

                // Reset course duration
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';

                // Fetch school details (Christmas dates, extra weeks) & Airports
                if (selectedSchoolId) {
                    // Fetch school details
                    const schoolDetailsUrl = routeConfig.schoolDetails.replace('SCHOOL_ID', selectedSchoolId);
                    fetch(schoolDetailsUrl, { cache: 'no-store' })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            extraAccommodationWeeks = data.extra_accommodation_weeks || 0;
                            schoolChristmasStartDate = data.christmas_start_date;
                            schoolChristmasEndDate = data.christmas_end_date;
                            console.log('Fetched school details:', { extraAccommodationWeeks, schoolChristmasStartDate, schoolChristmasEndDate });
                            updateChristmasSectionVisibility();

                            // Handle courier fee visibility
                            updateCourierFeeVisibility(data.courier_fee_enabled || false);

                            // Handle insurance visibility and pricing
                            updateInsuranceVisibility(data);
                        })
                        .catch(error => {
                            console.error('Error fetching school details:', error);
                            extraAccommodationWeeks = 0;
                            schoolChristmasStartDate = null;
                            schoolChristmasEndDate = null;
                            updateChristmasSectionVisibility();

                            // Hide courier fee on error
                            updateCourierFeeVisibility(false);
                        });

                    // Fetch Airports
                    const schoolAirportsUrl = routeConfig.schoolAirports.replace('SCHOOL_ID', selectedSchoolId);
                    fetch(schoolAirportsUrl, { cache: 'no-store' }) // New endpoint
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(airports => {
                            console.log('Fetched airports:', airports);
                            populateAirportDropdowns(airports); // Populate dropdowns
                            // Keep disabled until start date is selected
                            // arrivalAirportSelect.disabled = false; // REMOVED
                            // departureAirportSelect.disabled = false; // REMOVED
                        })
                        .catch(error => {
                            console.error('Error fetching airports:', error);
                            populateAirportDropdowns([]); // Clear dropdowns on error
                            arrivalAirportSelect.disabled = true; // Disable dropdowns
                            departureAirportSelect.disabled = true;
                        });

                    // Fetch Course Types
                    const schoolCourseTypesUrl = routeConfig.schoolCourseTypes.replace('SCHOOL_ID', selectedSchoolId);
                    fetch(schoolCourseTypesUrl, { cache: 'no-store' })
                        .then(response => {
                             if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                             return response.json();
                        })
                        .then(courseTypes => {
                            console.log('Fetched course types:', courseTypes);
                            populateCourseTypes(courseTypes);
                        })
                        .catch(error => {
                            console.error('Error fetching course types:', error);
                            populateCourseTypes([]);
                        });

                } else {
                     // No school selected, ensure Christmas section is hidden
                     updateChristmasSectionVisibility();
                     // Clear and disable airport dropdowns
                     populateAirportDropdowns([]);
                     arrivalAirportSelect.disabled = true;
                     departureAirportSelect.disabled = true;
                     // Hide insurance section when no school is selected
                     updateInsuranceVisibility(null);
                     // Clear course types
                     populateCourseTypes([]);
                }

                // Enable/disable course type and course dropdown based on school selection
                if (selectedSchoolId) {
                    // Enable course type dropdown (filtered by selected school)
                    // courseTypeSelect.disabled = false; // Handled by populateCourseTypes
                    // Enable accommodation dropdown
                    accommodationSelect.disabled = false;

                    // iOS-compatible filtering: rebuild courses & accommodations by school
                    filterOptions(courseSelect, 'data-school', selectedSchoolId);
                    filterAccommodationsForCourse(courseSelect.value);

                    // Rebuild course types to show only those available at the selected school
                    // rebuildCourseTypesForSchool(selectedSchoolId); // Replaced by AJAX
                } else {
                    // Disable course type, course and accommodation dropdowns if no school selected
                    courseTypeSelect.disabled = true;
                    courseSelect.disabled = true;
                    accommodationSelect.disabled = true;

                    // Clear dependent dropdowns via iOS-compatible rebuild to default-only
                    filterOptions(courseSelect, 'data-school', null);
                    filterOptions(accommodationSelect, 'data-school', null);

                    // Reset course types to default-only when no school is selected
                    rebuildCourseTypesForSchool(null);
                }

                // Disable all subsequent dropdowns
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // Airport dropdowns enabled/disabled based on fetch success/failure and start date selection
            });

            // --- Function to Update School Social Icons ---
            function updateSchoolSocials(schoolId) {
                const container = document.getElementById('breakdown-school-social-icons');
                if (!container) return;

                container.innerHTML = ''; // Clear previous icons

                if (!schoolId || !schoolSocials[schoolId] || schoolSocials[schoolId].length === 0) {
                    return;
                }

                const socials = schoolSocials[schoolId];
                
                // SVG Icons for platforms
                const icons = {
                    instagram: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-instagram"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>',
                    facebook: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-facebook"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
                    tiktok: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-music-2"><circle cx="8" cy="18" r="4"/><path d="M12 18V2l7 4"/></svg>', // Fallback for TikTok as Lucide doesn't have exact match in standard set, using music note or custom path
                    linkedin: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-linkedin"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/></svg>',
                    youtube: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-youtube"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>',
                    x: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-twitter"><path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-12.7 19.6a26 26 0 0 1-8-10.6c-.4-3.2 2.7-9.8 8-11.4 6-1.7 12.7 6.6 12.7 6.6z"/></svg>', // Using Twitter icon for X
                    website: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-globe"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>'
                };

                // Specific TikTok path override for better accuracy if desired, or keep music note
                icons.tiktok = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>';


                socials.forEach(account => {
                    if (account.platform && account.url) {
                        const a = document.createElement('a');
                        a.href = account.url;
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                        a.className = 'text-gray-500 hover:text-bayswater-blue transition-colors duration-200';
                        a.title = account.platform.charAt(0).toUpperCase() + account.platform.slice(1); // Capitalize tooltip
                        
                        // Insert Icon
                        a.innerHTML = icons[account.platform] || icons.website; // Default to globe if unknown

                        container.appendChild(a);
                    }
                });
            }

            // Function to render Factsheet/Course Detail buttons into a specific container
            function renderFactsheetButtons(courseId, containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;

                container.innerHTML = ''; // Clear previous buttons

                if (!courseId) return;

                let links = [];
                // Check for junior course links first (priority)
                if (juniorCourseDetailLinks[courseId] && juniorCourseDetailLinks[courseId].length > 0) {
                    links = juniorCourseDetailLinks[courseId];
                } 
                // Fallback to standard course links
                else if (courseDetailLinks[courseId] && courseDetailLinks[courseId].length > 0) {
                    links = courseDetailLinks[courseId];
                }

                if (links.length === 0) return;

                const globalButtonText = courseDetailsButtonText || 'Course details';

                links.forEach(link => {
                    if (link.url) {
                        const a = document.createElement('a');
                        a.href = link.url;
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                        // Match Search Accommodation button design (#003fbc background, white text)
                        a.className = 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:opacity-90 focus:outline-none focus:ring disabled:opacity-25 transition ease-in-out duration-150 mb-1 mr-1';
                        a.style.backgroundColor = '#003fbc';
                        a.textContent = link.button_text || globalButtonText;
                        
                        container.appendChild(a);
                    }
                });
            }

            // Function to update Factsheet buttons in the breakdown for both courses
            function updateBreakdownFactsheets() {
                const firstCourseId = document.getElementById('course_id').value;
                const secondCourseId = document.getElementById('second_course_id') ? document.getElementById('second_course_id').value : null;

                renderFactsheetButtons(firstCourseId, 'first-course-detail-buttons');
                
                // Only try to render second course buttons if the section exists/is visible
                if (secondCourseId) {
                    renderFactsheetButtons(secondCourseId, 'second-course-detail-buttons');
                }
            }

            // Expose for global use
            window.updateBreakdownFactsheets = updateBreakdownFactsheets;
            
            // Maintain backward compatibility for existing event listeners
            window.updateCourseDetailButtons = function() {
                updateBreakdownFactsheets();
            };

            // --- Function to Populate Airport Dropdowns ---
            function populateAirportDropdowns(airports = null) {
                // Update global cache if new airports provided
                if (airports !== null) {
                    currentSchoolAirports = airports;
                }

                const arrivalSelect = document.getElementById('arrival_transfer_airport_id');
                const departureSelect = document.getElementById('departure_transfer_airport_id');
                const courseTypeSelect = document.getElementById('course_type_id');
                const courseSelect = document.getElementById('course_id');

                const selectedCourseTypeId = courseTypeSelect.value ? parseInt(courseTypeSelect.value) : null;
                const selectedCourseId = courseSelect.value ? parseInt(courseSelect.value) : null;

                // Save current selections
                const currentArrival = arrivalSelect.value;
                const currentDeparture = departureSelect.value;

                // Clear existing options (except the default)
                arrivalSelect.innerHTML = '<option value="">-- Not Required --</option>';
                departureSelect.innerHTML = '<option value="">-- Not Required --</option>';

                if (currentSchoolAirports && currentSchoolAirports.length > 0) {
                    currentSchoolAirports.forEach(airport => {
                        let allowed = true;

                        // 1. Course Type Restriction
                        if (airport.restricted_course_type_ids && airport.restricted_course_type_ids.length > 0) {
                            // If restrictions exist, we must have a selected type and it must be allowed
                            if (!selectedCourseTypeId || !airport.restricted_course_type_ids.includes(selectedCourseTypeId)) {
                                allowed = false;
                            }
                        }

                        // 2. Course Restriction
                        if (allowed && airport.restricted_course_ids && airport.restricted_course_ids.length > 0) {
                            // If restrictions exist, we must have a selected course and it must be allowed
                            if (!selectedCourseId || !airport.restricted_course_ids.includes(selectedCourseId)) {
                                allowed = false;
                            }
                        }

                        if (allowed) {
                            const option = document.createElement('option');
                            option.value = airport.id;
                            option.textContent = airport.name;
                            arrivalSelect.appendChild(option.cloneNode(true));
                            departureSelect.appendChild(option.cloneNode(true));
                        }
                    });
                }
                
                // Restore selection if still valid (and exists in new options)
                if (currentArrival && arrivalSelect.querySelector(`option[value="${currentArrival}"]`)) {
                    arrivalSelect.value = currentArrival;
                }
                if (currentDeparture && departureSelect.querySelector(`option[value="${currentDeparture}"]`)) {
                    departureSelect.value = currentDeparture;
                }
                
                // Enable/Disable based on whether we have options
                // Always allow selecting "Not Required" if the list is empty? Or disable?
                // Previously: arrivalAirportSelect.disabled = true; if empty.
                const hasOptions = arrivalSelect.options.length > 1; 
                arrivalSelect.disabled = !hasOptions;
                departureSelect.disabled = !hasOptions;
            }

            // Add event listeners for shared data synchronization
            citySelect.addEventListener('change', function() {
                // Call synchronizeSharedData when city changes to update second course
                if (document.getElementById('second-course-section').style.display !== 'none') {
                    synchronizeSharedData();
                }
            });

            schoolSelect.addEventListener('change', function() {
                // Call synchronizeSharedData when school changes to update second course
                if (document.getElementById('second-course-section').style.display !== 'none') {
                    synchronizeSharedData();
                }
            });

            // Add event listener for course select
            courseSelect.addEventListener('change', function() {
                console.log('Course changed to: ' + this.value);

                // Update course detail buttons
                updateCourseDetailButtons(this.value);

                // Re-filter airport dropdowns based on new course selection
                populateAirportDropdowns();

                // Enable/disable start date and course duration based on course selection
                if (this.value) {
                    // Enable start date input
                    startDateInput.disabled = false;
                    // Enable course duration dropdown
                    courseDurationSelect.disabled = false;

                    // Update max weeks when course changes
                    updateMaxWeeks();

                    // Apply accommodation filtering based on selected course and restrictions
                    filterAccommodations();

                    applyJuniorCourseDateLimits(this.value);

                    // If fixed schedule course, restrict start dates to admin-defined schedules
                    const selectedCourseId = this.value;
                    const selectedOption = this.options[this.selectedIndex];
                    const pricingType = selectedOption ? selectedOption.getAttribute('data-pricing-type') : null;
                    if (pricingType === 'fixed_schedule') {
                        const schedules = (allCourseSchedules && allCourseSchedules[selectedCourseId]) ? allCourseSchedules[selectedCourseId] : [];
                        let allowedDates = schedules.map(s => s.start_date);
                        // Exclude Christmas period if configured
                        if (schoolChristmasStartDate && schoolChristmasEndDate) {
                            const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                            const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');
                            allowedDates = allowedDates.filter(d => {
                                const dt = new Date(d);
                                return !(dt >= christmasStart && dt <= christmasEnd);
                            });
                        }
                        if (typeof startDatePicker !== 'undefined' && startDatePicker) {
                            startDatePicker.set('enable', allowedDates);
                            // Clear invalid selection
                            if (startDateInput.value && !allowedDates.includes(startDateInput.value)) {
                                startDateInput.value = '';
                            }
                        }
                    } else {
                        // Clear explicit enable to revert to default Monday-only + Christmas logic
                        if (typeof startDatePicker !== 'undefined' && startDatePicker) {
                            // Unset any previous whitelist so default disable rules apply
                            startDatePicker.set('enable', null);
                        }
                    }
                } else {
                    // Disable start date and course duration if no course selected
                    startDateInput.disabled = true;
                    courseDurationSelect.disabled = true;
                }
                // Update Christmas section visibility (in case duration changes affect overlap)
                updateChristmasSectionVisibility();
                // No need to call autoCalculate here as it will be triggered by the course duration change
                if (birthdayInput.value) {
                    calculateAge(birthdayInput.value);
                }
            });

            // Function to synchronize accommodation duration with course duration
            function synchronizeAccommodationDuration() {
                // Only proceed if accommodation is selected
                if (!accommodationSelect.value) return;

                // Get the current values
                const courseDuration = parseInt(courseDurationSelect.value);
                const currentAccommodationDuration = parseInt(accommodationDurationSelect.value);

                // Only proceed if course duration is valid
                if (isNaN(courseDuration)) return;

                console.log('Synchronizing accommodation duration with course duration:', courseDuration);

                // Check if we need to repopulate the dropdown
                let needsRepopulation = false;

                // Case 1: Course duration increased beyond available options
                const maxAvailableOption = getMaxAvailableAccommodationWeek();
                if (courseDuration > maxAvailableOption) {
                    console.log('Course duration increased beyond available options. Max available:', maxAvailableOption);
                    needsRepopulation = true;
                }

                // Case 2: Accommodation duration exceeds course duration
                if (!isNaN(currentAccommodationDuration) && currentAccommodationDuration > courseDuration) {
                    console.log('Accommodation duration exceeds course duration');
                    needsRepopulation = true;
                }

                // Case 3: No accommodation duration selected
                if (isNaN(currentAccommodationDuration)) {
                    console.log('No accommodation duration selected');
                    needsRepopulation = true;
                }

                // Repopulate if needed
                if (needsRepopulation) {
                    console.log('Repopulating accommodation weeks dropdown');
                    populateAccommodationWeeks();
                    return; // populateAccommodationWeeks will handle selection
                }

                // If we get here, we don't need to repopulate, just update the existing options
                console.log('Updating existing accommodation duration options');

                // Disable options that exceed course duration
                Array.from(accommodationDurationSelect.options).forEach(option => {
                    const optionValue = parseInt(option.value);
                    if (!isNaN(optionValue)) {
                        // Disable options that exceed course duration
                        if (optionValue > courseDuration) {
                            option.disabled = true;
                            option.textContent = `${optionValue} week${optionValue > 1 ? 's' : ''} (exceeds course duration)`;
                        } else {
                            option.disabled = false;
                            option.textContent = `${optionValue} week${optionValue > 1 ? 's' : ''}`;
                        }
                    }
                });
            }

            // Helper function to get the maximum available accommodation week option
            function getMaxAvailableAccommodationWeek() {
                let max = 0;
                Array.from(accommodationDurationSelect.options).forEach(option => {
                    const value = parseInt(option.value);
                    if (!isNaN(value) && value > max) {
                        max = value;
                    }
                });
                return max;
            }

            // Add event listener for course duration select
            courseDurationSelect.addEventListener('change', function() {
                console.log('Course duration changed to: ' + this.value);

                // Synchronize accommodation duration with course duration
                synchronizeAccommodationDuration();

                // Update Christmas section visibility (existing functionality)
                updateChristmasSectionVisibility();
                updateSecondChristmasSectionVisibility();

                // Trigger calculation if needed (existing functionality)
                if (startDateInput.value && courseSelect.value && this.value) {
                    autoCalculate();
                }
            });

            // Add event listener for accommodation select
            accommodationSelect.addEventListener('change', function() {
                console.log('Accommodation changed to: ' + this.value);
                // Toggle accommodation duration visibility and enable/disable
                toggleAccommodationDuration(); // This now calls updateChristmasSectionVisibility internally
                toggleAccommodationOptions(); // Add this line to show/hide add-on checkboxes
            });

            // Add event listener for accommodation duration select
            accommodationDurationSelect.addEventListener('change', function() {
                console.log('Accommodation duration changed to: ' + this.value);
                updateChristmasSectionVisibility(); // Check overlap with new duration

                // Trigger calculation if needed
                if (startDateInput.value && courseSelect.value && courseDurationSelect.value) {
                     autoCalculate();
                }
            });

            // Add event listener for course type select
            courseTypeSelect.addEventListener('change', function() {
                console.log('Course type changed to: ' + this.value);
                
                // Re-filter airport dropdowns based on new course type selection
                populateAirportDropdowns();
                
                // Reset dependent selections
                courseSelect.value = '';
                courseDurationSelect.value = '';
                accommodationDurationSelect.value = '';
                
                const selectedSchoolId = schoolSelect.value;
                const selectedCourseTypeId = this.value;
                
                if (selectedSchoolId && selectedCourseTypeId) {
                    // iOS-compatible: rebuild courses filtered by school AND course type
                    filterOptions(courseSelect, ['data-school', 'data-course-type'], [selectedSchoolId, selectedCourseTypeId]);
                    // Enable course dropdown only if we have options beyond the default
                    courseSelect.disabled = courseSelect.options.length <= 1;
                } else {
                    // Clear courses when course type is not selected
                    filterOptions(courseSelect, 'data-school', null);
                    courseSelect.disabled = true;
                }
                
                // Disable course duration and accommodation duration
                courseDurationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;

                // Filter accommodations based on the new course type
                filterAccommodations();
                
                // Trigger calculation if needed
                if (startDateInput.value && courseSelect.value && courseDurationSelect.value) {
                    autoCalculate();
                }
            });


            // --- Accommodation Weeks Dropdown Population --- (Modified to respect course duration limits)
            function populateAccommodationWeeks() {
                console.log('Populating accommodation weeks dropdown');

                // Get the selected course duration
                const courseDuration = parseInt(courseDurationSelect.value) || 0;
                console.log('Selected course duration: ' + courseDuration + ' weeks');

                // Get the second course duration if available
                const secondCourseDuration = parseInt(secondCourseDurationSelect.value) || 0;
                console.log('Selected second course duration: ' + secondCourseDuration + ' weeks');

                // Calculate total course duration (first + second if selected)
                let totalCourseDuration = courseDuration;
                if (secondCourseDuration > 0) {
                    totalCourseDuration = courseDuration + secondCourseDuration;
                    console.log('Total course duration (first + second): ' + totalCourseDuration + ' weeks');
                }

                // Limit accommodation weeks to total course duration or use 52 as fallback
                let maxAccommodationWeeks = 52; // Default max if no course duration
                if (totalCourseDuration > 0) {
                    maxAccommodationWeeks = totalCourseDuration;
                }
                console.log('Max accommodation weeks: ' + maxAccommodationWeeks);

                // If no course duration or it's invalid, set a reasonable default
                if (!maxAccommodationWeeks || maxAccommodationWeeks <= 0) {
                    maxAccommodationWeeks = 52; // Default maximum of 52 weeks (1 year)
                    console.log('No valid course duration. Using default max: ' + maxAccommodationWeeks);
                }

                // Get the current selected value if any
                const currentValue = accommodationDurationSelect.value;
                const oldValue = '{{ old("accommodation_duration_weeks") }}';
                console.log('Current accommodation weeks value: ' + currentValue);
                console.log('Old accommodation weeks value from form: ' + oldValue);
                let valueSelected = false;

                // Populate dropdown options
                accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';

                // Generate options from 1 to maxAccommodationWeeks
                for (let i = 1; i <= maxAccommodationWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;

                    // Disable options that exceed total course duration if course is selected
                    if (totalCourseDuration > 0 && i > totalCourseDuration) {
                        option.disabled = true;
                        option.textContent = `${i} week${i > 1 ? 's' : ''} (exceeds course duration)`;
                    } else {
                        option.textContent = `${i} week${i > 1 ? 's' : ''}`;
                    }

                    // First try to select the current value if it matches
                    if (currentValue && parseInt(currentValue) === i) {
                        option.selected = true;
                        valueSelected = true;
                    }
                    // If no current value, try the old form value
                    else if (!valueSelected && oldValue && parseInt(oldValue) === i) {
                        option.selected = true;
                        valueSelected = true;
                    }

                    accommodationDurationSelect.appendChild(option);
                }

                console.log('Accommodation weeks dropdown populated with ' + maxAccommodationWeeks + ' options');

                // If no value was selected and we have options, select the total course duration (or first option if no course duration)
                if (!valueSelected && accommodationDurationSelect.options.length > 1) {
                    if (totalCourseDuration > 0) {
                        // Find the option that matches the total course duration
                        for (let i = 0; i < accommodationDurationSelect.options.length; i++) {
                            if (parseInt(accommodationDurationSelect.options[i].value) === totalCourseDuration) {
                                accommodationDurationSelect.selectedIndex = i;
                                console.log('Auto-selected accommodation duration to match total course duration:', totalCourseDuration);
                                valueSelected = true;
                                break;
                            }
                        }

                        // If no matching option was found, select the first option
                        if (!valueSelected) {
                            accommodationDurationSelect.selectedIndex = 1; // Select the first week option
                            console.log('No matching option for total course duration, selected first option');
                        }
                    } else {
                        accommodationDurationSelect.selectedIndex = 1; // Select the first week option
                        console.log('No course duration set, selected first accommodation duration option');
                    }
                }

                // Trigger change event if the value has changed
                if (currentValue && accommodationDurationSelect.value !== currentValue) {
                    console.log('Accommodation duration changed from', currentValue, 'to', accommodationDurationSelect.value);
                    accommodationDurationSelect.dispatchEvent(new Event('change'));
                }

                return valueSelected;
            }

            // --- Max Weeks Calculation & Dropdown Population --- (Keep existing function)
            function updateMaxWeeks() {
                const selectedCourseId = courseSelect.value;
                const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                const pricingType = selectedOption ? selectedOption.getAttribute('data-pricing-type') : null;
                let maxWeeks = 52; // Default max if no limit or no prices
                let minWeeks = 1; // Default min

                // Clear existing options and set default/disabled state
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';

                // If fixed schedule, populate durations from schedules
                if (pricingType === 'fixed_schedule' && selectedCourseId) {
                    const schedules = (allCourseSchedules && allCourseSchedules[selectedCourseId]) ? allCourseSchedules[selectedCourseId] : [];
                    const uniqueDurations = Array.from(new Set(schedules.map(s => parseInt(s.duration_weeks, 10)).filter(v => !isNaN(v)))).sort((a,b) => a - b);

                    if (uniqueDurations.length > 0) {
                        uniqueDurations.forEach(weeks => {
                            const option = document.createElement('option');
                            option.value = weeks;
                            option.textContent = `${weeks} week${weeks > 1 ? 's' : ''}`;
                            courseDurationSelect.appendChild(option);
                        });

                        // If a start date is selected and matches a schedule, lock duration to that schedule
                        if (startDateInput.value) {
                            const schedForDate = schedules.find(s => s.start_date === startDateInput.value);
                            if (schedForDate && schedForDate.duration_weeks) {
                                courseDurationSelect.value = String(parseInt(schedForDate.duration_weeks, 10));
                                courseDurationSelect.disabled = true; // lock to schedule duration
                            } else {
                                courseDurationSelect.disabled = false;
                            }
                        } else {
                            courseDurationSelect.disabled = false;
                            // Auto-select first option to prompt next steps
                            if (courseDurationSelect.options.length > 1) {
                                courseDurationSelect.selectedIndex = 1;
                                courseDurationSelect.dispatchEvent(new Event('change'));
                            }
                        }
                        return; // done for fixed schedule
                    } else {
                        // No schedules configured: keep dropdown disabled and show no options beyond default
                        courseDurationSelect.disabled = true;
                        return;
                    }
                }

                const juniorSettings = selectedCourseId && juniorCourseSettings && Object.prototype.hasOwnProperty.call(juniorCourseSettings, selectedCourseId)
                    ? juniorCourseSettings[selectedCourseId]
                    : null;

                if (juniorSettings) {
                    if (juniorSettings.min_weeks && Number.isInteger(juniorSettings.min_weeks)) {
                        minWeeks = Math.max(minWeeks, juniorSettings.min_weeks);
                    }
                    if (juniorSettings.max_weeks && Number.isInteger(juniorSettings.max_weeks)) {
                        maxWeeks = Math.min(maxWeeks, juniorSettings.max_weeks);
                    }

                    // Calculate remaining weeks based on start date and programme end date
                    if (juniorSettings.end_date && startDateInput.value) {
                        const startDate = new Date(startDateInput.value);
                        const endDate = new Date(juniorSettings.end_date);
                        
                        // Calculate difference in milliseconds
                        const diffTime = endDate.getTime() - startDate.getTime();
                        
                        if (diffTime >= 0) {
                            // Calculate weeks (using ceil to handle partial weeks which count as a full week in duration terms)
                            // e.g. Mon -> Fri is 4 days = 0.57 weeks -> 1 week
                            const weeksAvailable = Math.ceil(diffTime / (7 * 24 * 60 * 60 * 1000));
                            maxWeeks = Math.min(maxWeeks, weeksAvailable);
                        } else {
                            // Start date is after end date
                            maxWeeks = 0;
                        }
                    }
                }

                if (maxWeeks < minWeeks) {
                    // No valid duration available
                    courseDurationSelect.innerHTML = '<option value="">-- No valid duration --</option>';
                    courseDurationSelect.disabled = true;
                    return;
                }

                // Get the current selected value or old value
                let currentVal = courseDurationSelect.value ? parseInt(courseDurationSelect.value) : null;
                const oldValue = '{{ old("course_duration_weeks") }}';
                
                if (currentVal === null && oldValue) {
                    currentVal = parseInt(oldValue);
                }

                let valueSelected = false;

                // Populate dropdown options for per-week courses
                for (let i = minWeeks; i <= maxWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} week${i > 1 ? 's' : ''}`;

                    // Select if matches current value
                    if (currentVal === i) {
                        option.selected = true;
                        valueSelected = true;
                    }

                    courseDurationSelect.appendChild(option);
                }

                // If the previously selected value is now invalid (too high), select the max available
                if (!valueSelected && currentVal && currentVal > maxWeeks && maxWeeks >= minWeeks) {
                    courseDurationSelect.value = maxWeeks;
                    valueSelected = true;
                }

                // If we have a course selected but no duration selected, auto-select the first option
                if (selectedCourseId && !valueSelected && courseDurationSelect.options.length > 1) {
                    courseDurationSelect.selectedIndex = 1; // Select the first week option
                    // Trigger change event to update calculations
                    courseDurationSelect.dispatchEvent(new Event('change'));
                }
            }

            // --- Initialize Date of Birth with Default 18 Years Old --- (Keep existing logic)
            const birthdayInput = document.getElementById('client_birthday');
            const ageDisplay = document.getElementById('age-display');

            // Function to calculate date 18 years ago
            function getDefault18YearOldDate() {
                const today = new Date();
                const eighteenYearsAgo = new Date(today);
                eighteenYearsAgo.setFullYear(today.getFullYear() - 18);
                return eighteenYearsAgo.toISOString().split('T')[0]; // Format as YYYY-MM-DD
            }

            function calculateAge(birthDate, referenceDate = null) {
                const reference = referenceDate ? new Date(referenceDate) : 
                                 (startDateInput.value ? new Date(startDateInput.value) : new Date());
                const birth = new Date(birthDate);

                if (birth > reference) {
                    ageDisplay.textContent = 'Future date';
                    return 0;
                }

                let years = reference.getFullYear() - birth.getFullYear();

                let months = reference.getMonth() - birth.getMonth();
                if (months < 0) {
                    years--;
                    months += 12;
                }

                let days = reference.getDate() - birth.getDate();
                if (days < 0) {
                    months--;
                    if (months < 0) {
                        years--;
                        months += 12;
                    }

                    const lastMonth = new Date(reference.getFullYear(), reference.getMonth(), 0);
                    days += lastMonth.getDate();
                }

                let ageString = `Age: ${years}y`;
                if (months > 0 || days > 0) {
                    ageString += ` ${months}m`;
                }
                if (days > 0) {
                    ageString += ` ${days}d`;
                }

                if (startDateInput.value && !referenceDate) {
                    ageString += ` (at course start)`;
                }

                const selectedCourseId = courseSelect.value;
                if (selectedCourseId && juniorCourseSettings && Object.prototype.hasOwnProperty.call(juniorCourseSettings, selectedCourseId)) {
                    const settings = juniorCourseSettings[selectedCourseId];
                    if (settings) {
                        const minAge = Number.isInteger(settings.min_age) ? settings.min_age : null;
                        const maxAge = Number.isInteger(settings.max_age) ? settings.max_age : null;
                        const belowMin = minAge !== null && years < minAge;
                        const aboveMax = maxAge !== null && years > maxAge;
                        if (belowMin || aboveMax) {
                            if (minAge !== null && maxAge !== null) {
                                ageString += ` (allowed range ${minAge}-${maxAge})`;
                            } else if (minAge !== null) {
                                ageString += ` (minimum age ${minAge})`;
                            } else if (maxAge !== null) {
                                ageString += ` (maximum age ${maxAge})`;
                            }
                        }
                    }
                }

                ageDisplay.textContent = ageString;
                return years;
            }

            // Set default date if not already set
            if (!birthdayInput.value) {
                birthdayInput.value = getDefault18YearOldDate();
            }

            // Calculate and display initial age
            if (birthdayInput.value) {
                calculateAge(birthdayInput.value);
            }

            // Update age when date changes
            birthdayInput.addEventListener('change', function() {
                if (this.value) {
                    calculateAge(this.value);
                } else {
                    ageDisplay.textContent = '';
                }

                // Trigger auto-calculate if we have enough data
                if (courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            // --- Second Course Variables Declaration ---
            const secondCitySelect = document.getElementById('second_city_id');
            const secondSchoolSelect = document.getElementById('second_school_id');
            const secondCourseTypeSelect = document.getElementById('second_course_type_id');
            const secondCourseSelect = document.getElementById('second_course_id');
            const secondStartDateInput = document.getElementById('second_course_start_date');
            const secondCourseDurationSelect = document.getElementById('second_course_duration_weeks');
            const secondStartDateError = document.getElementById('second_start_date_error');

            // --- Initialize Flatpickr for Start Date --- (Keep existing logic, store instance)
            const currentYear = new Date().getFullYear();
            const startDatePicker = flatpickr(startDateInput, {
                minDate: `${currentYear}-01-01`, // Disable dates before current year
                maxDate: `${currentYear + 1}-12-31`, // Disable dates after next year
                disable: [
                    function(date) {
                        // Return true to disable date
                        // Disable weekends (0 = Sunday, 6 = Saturday) and non-Mondays (1 = Monday)
                        if (date.getDay() === 0 || date.getDay() > 1) {
                            return true;
                        }
                        
                        // Disable Christmas supplement dates if available
                        if (schoolChristmasStartDate && schoolChristmasEndDate) {
                            const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                            const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');
                            
                            // Check if date falls within Christmas supplement period
                            if (date >= christmasStart && date <= christmasEnd) {
                                return true;
                            }
                        }
                        
                        return false;
                    }
                ],
                dateFormat: "Y-m-d", // Ensure format matches HTML5 date input
                onChange: function(selectedDates, dateStr, instance) {
                    // Trigger change event manually for Flatpickr
                    startDateInput.dispatchEvent(new Event('change'));
                }
            });

            // Initialize flatpickr for second course start date with Monday-only and Christmas break logic (store instance)
            const secondStartDatePicker = flatpickr(secondStartDateInput, {
                minDate: `${currentYear}-01-01`, // Disable dates before current year
                maxDate: `${currentYear + 1}-12-31`, // Disable dates after next year
                disable: [
                    function(date) {
                        // Return true to disable date
                        // Disable weekends (0 = Sunday, 6 = Saturday) and non-Mondays (1 = Monday)
                        if (date.getDay() === 0 || date.getDay() > 1) {
                            return true;
                        }
                        
                        // Disable Christmas break dates if available
                        if (schoolChristmasStartDate && schoolChristmasEndDate) {
                            const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                            const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');
                            
                            // Check if date falls within Christmas break period
                            if (date >= christmasStart && date <= christmasEnd) {
                                return true;
                            }
                        }
                        
                        return false;
                    }
                ],
                dateFormat: "Y-m-d", // Ensure format matches HTML5 date input
                onChange: function(selectedDates, dateStr, instance) {
                    // Trigger change event manually for Flatpickr
                    secondStartDateInput.dispatchEvent(new Event('change'));
                    // Trigger auto-calculate when second course date changes
                    if (dateStr && validateSecondCourseStartDate()) {
                        autoCalculate();
                    }
                }
            });

            // --- Initial Filtering on Page Load --- (Keep existing logic)
            console.log('Initializing dropdowns on page load');

            // Show all options initially
            for (let option of citySelect.options) {
                if (option.value === "") continue;
                option.style.display = '';
            }
            for (let option of schoolSelect.options) {
                if (option.value === "") continue;
                option.style.display = '';
            }
            for (let option of courseSelect.options) {
                if (option.value === "") continue;
                option.style.display = '';
            }
            for (let option of accommodationSelect.options) {
                if (option.value === "") continue;
                option.style.display = '';
            }

            // Apply filters if values are already selected (e.g., after form validation)
            if (countrySelect.value) {
                console.log('Country already selected: ' + countrySelect.value);
                filterOptions(citySelect, 'data-country', countrySelect.value);
            }
            if (citySelect.value) {
                console.log('City already selected: ' + citySelect.value);
                filterOptions(schoolSelect, 'data-city', citySelect.value);
            }
            if (schoolSelect.value) {
                console.log('School already selected: ' + schoolSelect.value);
                // Trigger change event to fetch school details on load if school is pre-selected
                schoolSelect.dispatchEvent(new Event('change'));
                // The rest of the filtering will happen within the school change event handler
            } else {
                 // Initialize filtering if school not pre-selected
                 initializeFiltering();
            }

            // Initialize course duration weeks dropdown if course is selected
            // if (courseSelect.value) { // Moved inside school change handler logic
            //     console.log('Course already selected: ' + courseSelect.value);
            //     updateMaxWeeks();
            // }

            // Initialize accommodation duration dropdown
            // toggleAccommodationDuration(); // Initial visibility check // Moved inside school change handler logic

            // If accommodation is already selected (e.g., after form validation)
            // if (accommodationSelect.value) { // Moved inside school change handler logic
            //     console.log('Accommodation already selected: ' + accommodationSelect.value);
            //     populateAccommodationWeeks();
            // }

            // --- Auto-Calculate Function ---
            function autoCalculate() {
                console.log('Auto-calculate function called');
                console.log('Course:', courseSelect.value);
                console.log('Course Duration:', courseDurationSelect.value);

                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Check if we have the minimum required fields to calculate
                if (!courseSelect.value || !courseDurationSelect.value || !startDateInput.value || !regionSelect.value) {
                    console.log('Missing required fields, cannot calculate');
                    console.log('Region:', regionSelect.value);
                    console.log('Course:', courseSelect.value);
                    console.log('Course Duration:', courseDurationSelect.value);
                    console.log('Start Date:', startDateInput.value);
                    return; // Not enough data to calculate
                }

                // Check if accommodation is selected but no duration is chosen
                if (accommodationSelect.value && !accommodationDurationSelect.value) {
                    console.log('Accommodation selected but no duration chosen, using default of 1 week');
                    // Auto-select the first accommodation duration option
                    if (accommodationDurationSelect.options.length > 1) {
                        accommodationDurationSelect.selectedIndex = 1; // Select the first week option
                    }
                }

                console.log('Auto-calculating with course=' + courseSelect.value + ', duration=' + courseDurationSelect.value);

                // Show loading indicator
                if (resultsContainer) {
                    console.log('Showing loading indicator in results container');
                    resultsContainer.innerHTML = '';
                    resultsContainer.appendChild(loadingIndicator.cloneNode(true));
                } else {
                    console.error('Results container not found');
                }

                // Get form data
                const formData = new FormData(calculatorForm);

                // Add Christmas accommodation option IF VISIBLE
                if (christmasAccommodationDiv && christmasAccommodationDiv.style.display !== 'none') {
                    if (debugChristmasSettings) {
                        console.log('\n--- Christmas Accommodation in Calculation ---');
                        console.log('Christmas accommodation div display:', christmasAccommodationDiv.style.display);
                        console.log('Christmas accommodation selection:', christmasAccommodationSelect ? christmasAccommodationSelect.value : 'null');
                        console.log('Christmas extra weeks div display:', christmasExtraWeeksDiv ? christmasExtraWeeksDiv.style.display : 'null');
                        if (christmasExtraWeeksDiv && christmasExtraWeeksDiv.style.display !== 'none') {
                            console.log('Christmas extra weeks selection:', christmasExtraWeeksSelect ? christmasExtraWeeksSelect.value : 'null');
                        }
                    }

                    // Add the Christmas accommodation selection to the form data
                    if (christmasAccommodationSelect) {
                        formData.append('christmas_accommodation', christmasAccommodationSelect.value);
                        console.log('Adding Christmas accommodation option:', christmasAccommodationSelect.value);
                    }

                    // If Christmas accommodation is 'yes' and extra weeks are available AND VISIBLE, add them
                    if (christmasAccommodationSelect && christmasAccommodationSelect.value === 'yes' && extraAccommodationWeeks > 0 && christmasExtraWeeksDiv && christmasExtraWeeksDiv.style.display !== 'none') {
                        // Get the selected extra weeks value
                        let extraWeeksValue = (christmasExtraWeeksSelect ? christmasExtraWeeksSelect.value : null) || '1'; // Default to 1 if somehow not selected
                        formData.append('christmas_extra_weeks', extraWeeksValue);
                        console.log('Adding Christmas extra weeks:', extraWeeksValue);
                    }

                    // Add the Christmas dates to the form data for the calculation (if available)
                    if (schoolChristmasStartDate && schoolChristmasEndDate) {
                        formData.append('christmas_start_date', schoolChristmasStartDate);
                        formData.append('christmas_end_date', schoolChristmasEndDate);
                        console.log('Adding Christmas dates:', schoolChristmasStartDate, 'to', schoolChristmasEndDate);
                    }
                } else {
                     if (debugChristmasSettings) console.log('Christmas section not visible, not adding Christmas params to calculation.');
                     // Ensure Christmas params are not accidentally included if hidden
                     formData.delete('christmas_accommodation');
                     formData.delete('christmas_extra_weeks');
                     formData.delete('christmas_start_date');
                     formData.delete('christmas_end_date');
                }

                // Add selected airport transfers
                if (arrivalAirportSelect && arrivalAirportSelect.value) {
                    formData.append('arrival_transfer_airport_id', arrivalAirportSelect.value);
                }
                 if (departureAirportSelect && departureAirportSelect.value) {
                    formData.append('departure_transfer_airport_id', departureAirportSelect.value);
                }

                // Add insurance option if selected
                const insuranceCheckbox = document.getElementById('insurance');
                if (insuranceCheckbox && insuranceCheckbox.checked) {
                    formData.append('insurance_selected', '1');
                    console.log('Adding insurance option to calculation');
                }

                // Add selected nationality discounts
                const nationalityDiscountCheckboxes = document.querySelectorAll('.nationality-discount-checkbox:checked');
                if (nationalityDiscountCheckboxes.length > 0) {
                    nationalityDiscountCheckboxes.forEach(checkbox => {
                        formData.append('nationality_discounts[]', checkbox.value);
                    });
                    console.log('Adding nationality discounts to calculation:', Array.from(nationalityDiscountCheckboxes).map(cb => cb.value));
                }

                // Add second course data if visible and has values
                if (isSecondCourseVisible && secondCourseSelect && secondCourseSelect.value && secondStartDateInput && secondStartDateInput.value && secondCourseDurationSelect && secondCourseDurationSelect.value) {
                    console.log('Adding second course data to calculation');
                    formData.append('second_city_id', document.getElementById('second_city_id')?.value || '');
                    formData.append('second_school_id', document.getElementById('second_school_id')?.value || '');
                    formData.append('second_course_type_id', document.getElementById('second_course_type_id')?.value || '');
                    formData.append('second_course_id', secondCourseSelect.value);
                    formData.append('second_course_start_date', secondStartDateInput.value);
                    formData.append('second_course_duration_weeks', secondCourseDurationSelect.value);
                    console.log('Second course data added:', {
                        course_id: secondCourseSelect.value,
                        start_date: secondStartDateInput.value,
                        duration: secondCourseDurationSelect.value
                    });
                }

                // Add second accommodation data if visible and has values
                if (isSecondAccommodationVisible && secondAccommodationSelect && secondAccommodationSelect.value && secondAccommodationDurationSelect && secondAccommodationDurationSelect.value) {
                    console.log('Adding second accommodation data to calculation');
                    formData.append('second_accommodation_id', secondAccommodationSelect.value);
                    formData.append('second_accommodation_duration_weeks', secondAccommodationDurationSelect.value);
                    
                    // Add second accommodation options if selected
                    if (secondPrivateBathroomCheckbox && secondPrivateBathroomCheckbox.checked) {
                        formData.append('second_private_bathroom', '1');
                    }
                    if (secondDietarySupplementCheckbox && secondDietarySupplementCheckbox.checked) {
                        formData.append('second_dietary_supplement', '1');
                    }
                    // Add second Christmas accommodation parameter
                    const secondChristmasAccommodationSelect = document.getElementById('second_christmas_accommodation');
                    if (secondChristmasAccommodationSelect) {
                        formData.append('second_christmas_accommodation', secondChristmasAccommodationSelect.value);
                    }
                    
                    // Add Christmas extra weeks and dates if second Christmas accommodation is enabled
                    if (secondChristmasAccommodationSelect && secondChristmasAccommodationSelect.value === 'true') {
                        const secondChristmasExtraWeeksSelect = document.getElementById('second_christmas_extra_weeks');
                        const secondChristmasStartDateInput = document.getElementById('second_christmas_start_date');
                        const secondChristmasEndDateInput = document.getElementById('second_christmas_end_date');
                        
                        if (secondChristmasExtraWeeksSelect && secondChristmasExtraWeeksSelect.value) {
                            formData.append('second_christmas_extra_weeks', secondChristmasExtraWeeksSelect.value);
                        }
                        if (secondChristmasStartDateInput && secondChristmasStartDateInput.value) {
                            formData.append('second_christmas_start_date', secondChristmasStartDateInput.value);
                        }
                        if (secondChristmasEndDateInput && secondChristmasEndDateInput.value) {
                            formData.append('second_christmas_end_date', secondChristmasEndDateInput.value);
                        }
                    }
                    
                    console.log('Second accommodation data added:', {
                        accommodation_id: secondAccommodationSelect.value,
                        duration: secondAccommodationDurationSelect.value,
                        private_bathroom: secondPrivateBathroomCheckbox.checked,
                        dietary_supplement: secondDietarySupplementCheckbox.checked,
                        christmas_accommodation: secondChristmasAccommodationSelect ? secondChristmasAccommodationSelect.value : 'false'
                    });
                }

                // Debug form data
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }

                // Make sure CSRF token is included
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                  document.querySelector('input[name="_token"]')?.value;

                if (!csrfToken) {
                    console.error('CSRF token not found');
                }

                console.log('Sending AJAX request to:', calculatorForm.action);

                // Create a timeout promise
                const timeoutPromise = new Promise((_, reject) => {
                    setTimeout(() => reject(new Error('Request timed out')), 30000); // 30 second timeout
                });

                // Show loading indicator
                if (resultsContainer) {
                    resultsContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center p-8">
                            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-bayswater-blue mb-4"></div>
                            <p class="text-gray-600">Calculating...</p>
                        </div>
                    `;
                }

                // Send AJAX request with timeout
                // Use a variable to track if the request is still active
                let requestActive = true;

                // Add an event listener to detect page unload/navigation
                const unloadHandler = () => {
                    requestActive = false;
                };

                // Add the event listener before making the request
                window.addEventListener('beforeunload', unloadHandler);

                Promise.race([
                    fetch(calculatorForm.action, {
                        method: 'POST',
                        cache: 'no-store',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin' // Include cookies
                    }),
                    timeoutPromise
                ])
                .then(response => {
                    // Check if the request is still active
                    if (!requestActive) {
                        console.log('Request was cancelled due to page navigation');
                        return null; // Return null to skip further processing
                    }

                    console.log('Response received:', response.status);

                    // If we get a 422 validation error, parse the JSON response to get validation errors
                    if (response.status === 422) {
                        return response.json().then(errors => {
                            throw new ValidationError('Validation failed', errors);
                        });
                    }

                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }

                    // Expect JSON now
                    return response.json();
                })
                .then(data => {
                    // Check if the request is still active or if data is null (cancelled)
                    if (!requestActive || data === null) {
                        console.log('Request was cancelled or response is null, skipping processing');
                        return; // Skip processing
                    }

                    if (data.costBreakdown) {
                        console.log('JSON response received, rendering results.');
                        renderResults(data.costBreakdown); // Call function to render results from JSON
                    } else {
                         console.error('Cost breakdown data not found in JSON response');
                         resultsContainer.innerHTML = '<div class="p-4 text-red-600">Error processing the calculation response. Please try again.</div>';
                    }

                })
                .catch(error => {
                    // Check if the request is still active
                    if (!requestActive) {
                        console.log('Request was cancelled due to page navigation, ignoring error');
                        return; // Skip error handling
                    }

                    console.error('Error calculating:', error);

                    if (resultsContainer) {
                        // Check if this is a validation error
                        if (error.name === 'ValidationError' && error.errors) {
                            let errorHtml = `<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <p class="font-bold mb-2">Please correct the following errors:</p>
                                <ul class="list-disc pl-5">`;

                            // Add each validation error to the list
                            for (const field in error.errors.errors) {
                                error.errors.errors[field].forEach(message => {
                                    errorHtml += `<li>${message}</li>`;
                                });
                            }

                            errorHtml += `</ul></div>`;
                            resultsContainer.innerHTML = errorHtml;
                        } else {
                            // Generic error
                            resultsContainer.innerHTML = `<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                                <p class="font-bold">Error calculating</p>
                                <p>${error.message || 'Please try again or contact support if the problem persists.'}</p>
                            </div>`;
                        }
                    }
                })
                .finally(() => {
                    // Remove the event listener when the request is complete
                    window.removeEventListener('beforeunload', unloadHandler);
                });
            }

            // --- Add event listeners for auto-calculation ---
            // Add event listener for region dropdown
            document.getElementById('region_id').addEventListener('change', function() {
                console.log('Region changed to: ' + this.value);

                // Reset all dependent dropdowns
                countrySelect.value = '';
                citySelect.value = '';
                schoolSelect.value = '';
                courseSelect.value = '';
                startDateInput.value = '';
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';
                accommodationSelect.value = '';
                accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';
                populateAirportDropdowns([]); // Clear airport dropdowns
                arrivalAirportSelect.disabled = true; // Disable airport dropdowns
                departureAirportSelect.disabled = true;

                // Hide accommodation duration and Christmas section
                toggleAccommodationDuration(); // This will also call updateChristmasSectionVisibility

                // Enable/disable country dropdown based on region selection
                if (this.value) {
                    // Enable country dropdown
                    countrySelect.disabled = false;
                    // iOS-compatible: show all country options (no region attribute available)
                    filterOptions(countrySelect, '__ALL__', '__ALL__');
                } else {
                    // Disable country dropdown if no region selected and clear options
                    countrySelect.disabled = true;
                    filterOptions(countrySelect, '__ALL__', null);
                }

                // Disable all subsequent dropdowns and rebuild to default-only
                citySelect.disabled = true;
                schoolSelect.disabled = true;
                courseSelect.disabled = true;
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // arrivalAirportSelect.disabled = true; // Already disabled above
                // departureAirportSelect.disabled = true;

                // Rebuild dependent dropdowns to default-only to avoid auto-selection
                filterOptions(citySelect, '__ALL__', null);
                filterOptions(schoolSelect, '__ALL__', null);
                filterOptions(courseSelect, '__ALL__', null);
                filterOptions(accommodationSelect, '__ALL__', null);

                // Do not auto-calculate on region change
            });



            // For start date, we'll check if all required fields are filled before calculating
            startDateInput.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');
                // const extraAccommodationWeeks = {{ $school->extra_accommodation_weeks ?? 0 }}; // Use fetched value

                // Recalculate age when start date changes
                if (birthdayInput.value && this.value) {
                    calculateAge(birthdayInput.value);
                }

                // Update max weeks based on the new start date (for Junior Courses)
                updateMaxWeeks();

                // Update Christmas section visibility based on the new date
                updateChristmasSectionVisibility();
                updateSecondChristmasSectionVisibility();

                // Update accommodation add-ons when start date changes (year-specific logic)
                updateAccommodationAddOns();

                // If fixed schedule course, lock duration to the schedule for the selected date
                const selectedOption = courseSelect.options[courseSelect.selectedIndex];
                const pricingType = selectedOption ? selectedOption.getAttribute('data-pricing-type') : null;
                if (pricingType === 'fixed_schedule' && courseSelect.value) {
                    const schedules = (allCourseSchedules && allCourseSchedules[courseSelect.value]) ? allCourseSchedules[courseSelect.value] : [];
                    const schedForDate = schedules.find(s => s.start_date === startDateInput.value);
                    if (schedForDate && schedForDate.duration_weeks) {
                        courseDurationSelect.value = String(parseInt(schedForDate.duration_weeks, 10));
                        courseDurationSelect.disabled = true;
                    } else {
                        courseDurationSelect.disabled = false;
                    }
                }

                // Enable airport dropdowns if school is also selected
                if (schoolSelect.value && this.value) {
                    arrivalAirportSelect.disabled = false;
                    departureAirportSelect.disabled = false;
                } else {
                    arrivalAirportSelect.disabled = true;
                    departureAirportSelect.disabled = true;
                }

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            // For other fields, only calculate if we have the core required fields
            accommodationSelect.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Log the accommodation selection
                if (debugChristmasSettings) {
                    console.log('Accommodation changed to:', this.value);
                    if (this.selectedIndex >= 0) {
                        console.log('Selected accommodation:', this.options[this.selectedIndex].text);
                        console.log('data-requires-christmas-supplement:', this.options[this.selectedIndex].getAttribute('data-requires-christmas-supplement'));
                    }
                }

                // Update accommodation add-ons based on year-specific settings
                updateAccommodationAddOns();

                // Toggle duration and update Christmas visibility
                toggleAccommodationDuration(); // This calls updateChristmasSectionVisibility

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            accommodationDurationSelect.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Update Christmas visibility based on new duration
                updateChristmasSectionVisibility();

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            document.getElementById('courier_fee_option').addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            // Add event listeners for airport transfer dropdowns
            arrivalAirportSelect.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            departureAirportSelect.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });


            // Add event listener for Christmas accommodation dropdown
            christmasAccommodationSelect.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');
                // Using the extraAccommodationWeeks variable declared at the top level

                console.log('Christmas accommodation changed to:', this.value);
                console.log('Extra accommodation weeks:', extraAccommodationWeeks);

                // Show/hide extra weeks based on selection
                populateChristmasExtraWeeks(extraAccommodationWeeks); // This handles visibility now

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            // Add event listener for Christmas extra weeks dropdown
            christmasExtraWeeksSelect.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            // Add event listener for private bathroom checkbox
            const privateBathroomCheckbox = document.getElementById('private_bathroom');
            if (privateBathroomCheckbox) {
                privateBathroomCheckbox.addEventListener('change', function() {
                    // Get the region dropdown
                    const regionSelect = document.getElementById('region_id');

                    // Only auto-calculate if we have all required fields
                    if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                        autoCalculate();
                    }
                });
            }

            // Add event listener for dietary supplement checkbox
            const dietarySupplementCheckbox = document.getElementById('dietary_supplement');
            if (dietarySupplementCheckbox) {
                dietarySupplementCheckbox.addEventListener('change', function() {
                    // Get the region dropdown
                    const regionSelect = document.getElementById('region_id');

                    // Only auto-calculate if we have all required fields
                    if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                        autoCalculate();
                    }
                });
            }

            // Add event listener for insurance checkbox
            const insuranceCheckbox = document.getElementById('insurance_selected');
            if (insuranceCheckbox) {
                insuranceCheckbox.addEventListener('change', function() {
                    // Get the region dropdown
                    const regionSelect = document.getElementById('region_id');

                    // Only auto-calculate if we have all required fields
                    if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                        autoCalculate();
                    }
                });
            }

            // Add event listeners for nationality discount checkboxes
            const nationalityDiscountCheckboxes = document.querySelectorAll('.nationality-discount-checkbox');
            nationalityDiscountCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Get the region dropdown
                    const regionSelect = document.getElementById('region_id');

                    // Only auto-calculate if we have all required fields
                    if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                        autoCalculate();
                    }
                });
            });

            // Dynamic visibility for Special Discount For section
            const specialDiscountSection = document.getElementById('special-discount-section');
            function updateSpecialDiscountVisibility() {
                const regionId = document.getElementById('region_id')?.value;
                const schoolId = document.getElementById('school_id')?.value;
                const courseId = document.getElementById('course_id')?.value;
                const courseTypeId = document.getElementById('course_type_id')?.value;
                const accommodationId = document.getElementById('accommodation_id')?.value;
                const startDate = document.getElementById('course_start_date')?.value;
                const durationWeeks = document.getElementById('course_duration_weeks')?.value;
                const nationalityCountryId = document.getElementById('country_id')?.value;

                if (!regionId || !schoolId || !startDate) {
                    // Hide and clear selections if core conditions missing
                    if (specialDiscountSection) {
                        specialDiscountSection.style.display = 'none';
                        document.querySelectorAll('.nationality-discount-checkbox').forEach(cb => cb.checked = false);
                    }
                    return;
                }

                const params = new URLSearchParams({
                    region_id: regionId,
                    school_id: schoolId,
                    course_id: courseId || '',
                    course_type_id: courseTypeId || '',
                    accommodation_id: accommodationId || '',
                    course_start_date: startDate,
                    course_duration_weeks: durationWeeks || '',
                    nationality_country_id: nationalityCountryId || '',
                });

                fetch(`/calculator/nationality-discounts/check?${params.toString()}`, {
                    cache: 'no-store',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(data => {
                    const applicable = Array.isArray(data.applicable) ? data.applicable.map(d => String(d.id)) : [];
                    const checkboxes = document.querySelectorAll('.nationality-discount-checkbox');

                    let hasApplicable = false;
                    checkboxes.forEach(cb => {
                        const id = cb.value;
                        const isEligible = applicable.includes(id);
                        cb.closest('div').style.display = isEligible ? '' : 'none';
                        if (!isEligible) cb.checked = false;
                        if (isEligible) hasApplicable = true;
                    });

                    if (specialDiscountSection) {
                        specialDiscountSection.style.display = hasApplicable ? '' : 'none';
                    }

                    // If visibility changed to hidden, trigger recalculation to remove effects
                    if (!hasApplicable) {
                        if (regionId && courseId && durationWeeks && startDate) {
                            autoCalculate();
                        }
                    }
                })
                .catch(err => {
                    console.error('Failed to check nationality discounts:', err);
                    if (specialDiscountSection) specialDiscountSection.style.display = 'none';
                    document.querySelectorAll('.nationality-discount-checkbox').forEach(cb => cb.checked = false);
                });
            }

            // Hook updates to relevant input changes
            ['region_id','school_id','course_id','course_type_id','accommodation_id','course_start_date','course_duration_weeks','country_id'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', updateSpecialDiscountVisibility);
                    el.addEventListener('input', updateSpecialDiscountVisibility);
                }
            });

            // Initialize on page load
            updateSpecialDiscountVisibility();

            // Calculate button and form submission event listener removed as calculations are now automatic

            // Initialize filtering and disabled states on page load
            function initializeFiltering() {
                console.log('Initializing filtering on page load');

                // Default state: only region is enabled, all other dropdowns are disabled
                countrySelect.disabled = true;
                citySelect.disabled = true;
                schoolSelect.disabled = true;
                courseSelect.disabled = true;
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                arrivalAirportSelect.disabled = true; // Disable airport dropdowns initially
                departureAirportSelect.disabled = true;

                // Rebuild country dropdown to default-only when empty
                if (!countrySelect.value) {
                    filterOptions(countrySelect, '__ALL__', null);
                }

                // Clear all dropdowns to prevent auto-selection
                if (!citySelect.value) {
                    filterOptions(citySelect, '__ALL__', null);
                }

                if (!schoolSelect.value) {
                    filterOptions(schoolSelect, '__ALL__', null);
                }

                if (!courseSelect.value) {
                    filterOptions(courseSelect, '__ALL__', null);
                }

                if (!accommodationSelect.value) {
                    filterOptions(accommodationSelect, '__ALL__', null);
                }

                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // If region is pre-selected, enable country dropdown
                if (regionSelect.value) {
                    console.log('Region is pre-selected, enabling country dropdown');
                    countrySelect.disabled = false;

                    // Show all country options
                    for (let i = 0; i < countrySelect.options.length; i++) {
                        const option = countrySelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = '';
                    }

                    // If country is pre-selected, enable city dropdown
                    if (countrySelect.value) {
                        console.log('Country is pre-selected, enabling city dropdown');
                        citySelect.disabled = false;
                        // Only filter options if city is already selected
                        if (citySelect.value) {
                            filterOptions(citySelect, 'data-country', countrySelect.value);

                        }

                        // If city is pre-selected, enable school dropdown
                        if (citySelect.value) {
                            console.log('City is pre-selected, enabling school dropdown');
                            schoolSelect.disabled = false;

                            // Only filter options if school is already selected
                            if (schoolSelect.value) {
                                filterOptions(schoolSelect, 'data-city', citySelect.value);

                                // If school is pre-selected, enable course and accommodation dropdowns
                                console.log('School is pre-selected, enabling course and accommodation dropdowns');
                                courseSelect.disabled = false;
                                accommodationSelect.disabled = false;
                                // Airport dropdowns enabled in school change listener

                                // Only filter options if course or accommodation is already selected
                                if (courseSelect.value) {
                                    filterOptions(courseSelect, 'data-school', schoolSelect.value);

                                    // If course is pre-selected, enable start date and course duration
                                    console.log('Course is pre-selected, enabling start date and course duration');
                                    startDateInput.disabled = false;
                                    courseDurationSelect.disabled = false;
                                    updateMaxWeeks();
                                }

                                if (accommodationSelect.value) {
                                    filterOptions(accommodationSelect, 'data-school', schoolSelect.value);

                                    // If accommodation is pre-selected, enable accommodation duration
                                    console.log('Accommodation is pre-selected, enabling accommodation duration');
                                    accommodationDurationSelect.disabled = false;
                                    toggleAccommodationDuration();
                                }
                            }
                        }
                    }
                }
            }

            // Initialize filtering
            // initializeFiltering(); // Call this AFTER the school change event listener is set up

            // Function to update Christmas accommodation option visibility (REMOVED - use updateChristmasSectionVisibility)
            // function updateChristmasAccommodation() { ... }

            // Call the debug function if debug mode is enabled
            if (debugChristmasSettings) {
                debugChristmasSettings();
            }

            // Function to initialize Christmas extra weeks dropdown (REMOVED - use populateChristmasExtraWeeks)
            // function initializeChristmasExtraWeeks() { ... }

            // Initialize Christmas extra weeks dropdown on page load (REMOVED - handled by school change/visibility update)
            // initializeChristmasExtraWeeks();

            // Run auto-calculate on page load if we have enough data (REMOVED - calculation triggered by events)
            // if (courseSelect.value && courseDurationSelect.value && startDateInput.value) {
            //     autoCalculate();
            // }

            // Initialize Christmas section visibility on page load if accommodation is already selected
            if (accommodationSelect.value) {
                console.log('Initializing Christmas section visibility on page load for accommodation:', accommodationSelect.value);
                updateChristmasSectionVisibility();
            }

            // --- PDF and Print Functionality --- (Keep existing logic)
            // Get references to the forms (these always exist)
            const pdfForm = document.getElementById('pdf-form');
            const printForm = document.getElementById('print-form');

            // Function to copy form data to the target form
            function copyFormData(targetForm) {
                // Clear any existing inputs
                targetForm.innerHTML = '';
                // Add CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                  document.querySelector('input[name="_token"]')?.value;
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                targetForm.appendChild(csrfInput);

                // Get all form fields from the calculator form
                const formData = new FormData(calculatorForm);

                 // Manually add Christmas data if the section is visible
                 if (christmasAccommodationDiv.style.display !== 'none') {
                     formData.set('christmas_accommodation', christmasAccommodationSelect.value);
                     if (christmasAccommodationSelect.value === 'yes' && extraAccommodationWeeks > 0 && christmasExtraWeeksDiv.style.display !== 'none') {
                         formData.set('christmas_extra_weeks', christmasExtraWeeksSelect.value || '1');
                     }
                     if (schoolChristmasStartDate) formData.set('christmas_start_date', schoolChristmasStartDate);
                     if (schoolChristmasEndDate) formData.set('christmas_end_date', schoolChristmasEndDate);
                 } else {
                     // Ensure Christmas params are removed if hidden
                     formData.delete('christmas_accommodation');
                     formData.delete('christmas_extra_weeks');
                     formData.delete('christmas_start_date');
                     formData.delete('christmas_end_date');
                 }


                // Create hidden inputs for each form field
                for (const [name, value] of formData.entries()) {
                    if (name === '_token') continue; // Skip CSRF token as we already added it

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    targetForm.appendChild(input);
                }

                // Handle second course data if visible
                if (typeof isSecondCourseVisible !== 'undefined' && isSecondCourseVisible) {
                    const secondCourseFields = {
                        'second_city_id': document.getElementById('second_city_id')?.value || '',
                        'second_school_id': document.getElementById('second_school_id')?.value || '',
                        'second_course_type_id': document.getElementById('second_course_type_id')?.value || '',
                        'second_course_id': document.getElementById('second_course_id')?.value || '',
                        'second_course_start_date': document.getElementById('second_course_start_date')?.value || '',
                        'second_course_duration_weeks': document.getElementById('second_course_duration_weeks')?.value || ''
                    };

                    for (const [name, value] of Object.entries(secondCourseFields)) {
                        if (value) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = name;
                            input.value = value;
                            targetForm.appendChild(input);
                        }
                    }
                }
                // Append exchange settings from localStorage
                try {
                    const ls = window.localStorage;
                    const exEnabled = ls.getItem('exchange_enabled') === '1' ? '1' : '0';
                    const exRate = ls.getItem('exchange_rate') || '';
                    const exCurrency = ls.getItem('exchange_target_currency') || '';
                    const fields = [
                        ['exchange_enabled', exEnabled],
                        ['exchange_rate', exRate],
                        ['exchange_target_currency', exCurrency]
                    ];
                    fields.forEach(([name, value]) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        input.value = value;
                        targetForm.appendChild(input);
                    });
                } catch (e) {
                    console.warn('Failed to attach exchange settings to form', e);
                }
            }

            // Function to initialize print and PDF buttons
            function initializePrintAndPdfButtons() {
                console.log('Initializing print and PDF buttons');

                // Get the buttons (these only exist after a calculation)
                const printButton = document.getElementById('print-quote');
                const pdfButton = document.getElementById('download-pdf');

                // Only proceed if the buttons exist
                if (printButton && pdfButton) {
                    console.log('Print and PDF buttons found');

                    // Remove any existing event listeners (to prevent duplicates)
                    printButton.replaceWith(printButton.cloneNode(true));
                    const newPrintButton = document.getElementById('print-quote');

                    newPrintButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Print button clicked');
                        copyFormData(printForm);
                        console.log('Print form data copied, submitting form...');
                        printForm.submit();
                    });

                    pdfButton.replaceWith(pdfButton.cloneNode(true));
                    const newPdfButton = document.getElementById('download-pdf');

                    newPdfButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('PDF button clicked');
                        copyFormData(pdfForm);
                        console.log('PDF form data copied, submitting form...');
                        pdfForm.submit();
                    });
                } else {
                    console.log('Print and PDF buttons not found (calculation not yet performed)');
                }
            }

            // Initialize the buttons on page load (only if they exist)
            initializePrintAndPdfButtons();

            // Debug function to check Christmas settings in the database (Keep existing function)
            function debugChristmasSettings() {
                console.log('\n=== CHRISTMAS SETTINGS DEBUG ===');
                console.log('School ID:', {{ $school->id ?? 'null' }});
                console.log('School Name:', '{{ $school->name ?? "Not set" }}');
                console.log('Christmas Fee Per Week:', {{ $school->christmas_fee_per_week ?? 0 }});
                console.log('Christmas Start Date:', '{{ $school->christmas_start_date ? $school->christmas_start_date->format("Y-m-d") : "Not set" }}');
                console.log('Christmas End Date:', '{{ $school->christmas_end_date ? $school->christmas_end_date->format("Y-m-d") : "Not set" }}');
                console.log('Extra Accommodation Weeks:', extraAccommodationWeeks);
                console.log('================================');
            }

            // Call the debug function if debug mode is enabled
            if (debugChristmasSettings) {
                debugChristmasSettings();
            }

            // DIAGNOSTIC: Function to check CSS rules affecting an element (Keep existing function)
            function checkCssRules(elementId) {
                const element = document.getElementById(elementId);
                if (!element) {
                    console.log(`Element with ID '${elementId}' not found`);
                    return;
                }

                console.log(`\nCSS rules affecting element with ID '${elementId}':`);

                // Get all stylesheets
                const styleSheets = document.styleSheets;
                let affectingRules = [];

                try {
                    // Loop through all stylesheets
                    for (let i = 0; i < styleSheets.length; i++) {
                        const styleSheet = styleSheets[i];
                        try {
                            // Get all rules in the stylesheet
                            const rules = styleSheet.cssRules || styleSheet.rules;

                            // Loop through all rules
                            for (let j = 0; j < rules.length; j++) {
                                const rule = rules[j];

                                // Check if the rule applies to the element
                                if (rule.selectorText && element.matches(rule.selectorText)) {
                                    affectingRules.push({
                                        selector: rule.selectorText,
                                        cssText: rule.cssText,
                                        styleSheet: styleSheet.href || 'inline style'
                                    });
                                }
                            }
                        } catch (e) {
                            console.log(`Could not access rules in stylesheet ${i}:`, e.message);
                        }
                    }
                } catch (e) {
                    console.log('Error accessing stylesheets:', e.message);
                }

                // Log the affecting rules
                if (affectingRules.length > 0) {
                    console.log('Found', affectingRules.length, 'CSS rules affecting this element:');
                    affectingRules.forEach((rule, index) => {
                        console.log(`${index + 1}. Selector: ${rule.selector}`);
                        console.log(`   CSS: ${rule.cssText}`);
                        console.log(`   Source: ${rule.styleSheet}`);
                    });
                } else {
                    console.log('No CSS rules found that specifically target this element.');
                }

                // Log the computed style
                const computedStyle = window.getComputedStyle(element);
                console.log('\nComputed style for display property:', computedStyle.display);

                // Check if any parent elements have display: none
                let parent = element.parentElement;
                while (parent) {
                    const parentStyle = window.getComputedStyle(parent);
                    if (parentStyle.display === 'none') {
                        console.log(`Parent element with tag ${parent.tagName} and ID ${parent.id || 'none'} has display: none`);
                    }
                    parent = parent.parentElement;
                }
            }

            // DIAGNOSTIC: Function to inspect HTML structure and element IDs (Keep existing function)
            function inspectHtmlStructure() {
                console.log('\n=== INSPECTING HTML STRUCTURE ===');

                // Check if elements exist
                const christmasAccommodationDivExists = document.getElementById('christmas_accommodation_div') !== null;
                const christmasAccommodationSelectExists = document.getElementById('christmas_accommodation') !== null;
                const christmasExtraWeeksDivExists = document.getElementById('christmas_extra_weeks_div') !== null;
                const christmasExtraWeeksSelectExists = document.getElementById('christmas_extra_weeks') !== null;

                console.log('Element existence check:');
                console.log('- christmas_accommodation_div exists:', christmasAccommodationDivExists);
                console.log('- christmas_accommodation select exists:', christmasAccommodationSelectExists);
                console.log('- christmas_extra_weeks_div exists:', christmasExtraWeeksDivExists);
                console.log('- christmas_extra_weeks select exists:', christmasExtraWeeksSelectExists);

                // Check element properties if they exist
                if (christmasAccommodationDivExists) {
                    const div = document.getElementById('christmas_accommodation_div');
                    console.log('\nchristmas_accommodation_div properties:');
                    console.log('- display style:', div.style.display);
                    console.log('- computed display:', window.getComputedStyle(div).display);
                    console.log('- innerHTML length:', div.innerHTML.length);
                    console.log('- outerHTML:', div.outerHTML);
                }

                if (christmasExtraWeeksDivExists) {
                    const div = document.getElementById('christmas_extra_weeks_div');
                    console.log('\nchristmas_extra_weeks_div properties:');
                    console.log('- display style:', div.style.display);
                    console.log('- computed display:', window.getComputedStyle(div).display);
                    console.log('- innerHTML length:', div.innerHTML.length);
                    console.log('- outerHTML:', div.outerHTML);
                }

                // Check parent-child relationships
                if (christmasAccommodationDivExists && christmasExtraWeeksDivExists) {
                    const accommodationDiv = document.getElementById('christmas_accommodation_div');
                    const extraWeeksDiv = document.getElementById('christmas_extra_weeks_div');

                    console.log('\nParent-child relationship:');
                    console.log('- Is extraWeeksDiv a child of accommodationDiv:', accommodationDiv.contains(extraWeeksDiv));
                }

                // Check school settings
                // const extraAccommodationWeeks = {{ $school->extra_accommodation_weeks ?? 0 }}; // Use fetched value
                console.log('\nSchool settings (from JS):');
                console.log('- Extra accommodation weeks:', extraAccommodationWeeks);
                console.log('- Christmas start date:', schoolChristmasStartDate);
                console.log('- Christmas end date:', schoolChristmasEndDate);

                console.log('================================');

                // Check CSS rules affecting the Christmas accommodation elements
                if (christmasAccommodationDivExists) {
                    checkCssRules('christmas_accommodation_div');
                }

                if (christmasExtraWeeksDivExists) {
                    checkCssRules('christmas_extra_weeks_div');
                }
            }

            // Function to force Christmas options to be visible (REMOVED - not needed with new logic)
            // function forceChristmasOptions() { ... }

            // Initialize Christmas options when the page loads (REMOVED - handled by school change/visibility update)
            // if (startDateInput.value && accommodationSelect.value) { ... }

            // Add event listener to accommodation select to ensure Christmas options are visible (REMOVED - handled by school change/visibility update)
            // accommodationSelect.addEventListener('change', function() { ... });

            // Helper function to populate the Christmas extra weeks dropdown (REMOVED - use populateChristmasExtraWeeks)
            // function populateChristmasExtraWeeks(extraWeeks) { ... }

            // DIAGNOSTIC: Function to try alternative approaches to showing the Christmas elements (REMOVED)
            // function tryAlternativeApproaches() { ... }

            // Only try alternative approaches if in Christmas period (REMOVED)
            // if (extraAccommodationWeeks > 0 && startDateInput.value && isChristmasPeriod(startDateInput.value)) { ... }

            // Function to check if a date is within the Christmas period (REMOVED - use checkChristmasOverlap)
            // function isChristmasPeriod(date) { ... }

            // Function to update Christmas accommodation option visibility (REMOVED - use updateChristmasSectionVisibility)
            // function updateChristmasAccommodation() { ... }

            // Function to populate the Christmas extra weeks dropdown (REMOVED - use populateChristmasExtraWeeks)
            // function populateChristmasExtraWeeks(extraWeeks) { ... }

            // Function to update the Christmas extra weeks dropdown (REMOVED - handled by populateChristmasExtraWeeks and visibility update)
            // function updateChristmasExtraWeeksDropdown() { ... }

            // --- Function to Render Results from JSON ---
            function renderResults(costBreakdown) {
                if (!resultsContainer) {
                    console.error('Results container not found for rendering.');
                    return;
                }
                console.log('Rendering results with costBreakdown:', JSON.stringify(costBreakdown, null, 2)); // Log received data

                // Helper to format currency (exchange-aware)
                const getCurrencySymbolLocal = (code) => {
                    switch (code) {
                        case 'GBP': return '£';
                        case 'EUR': return '€';
                        case 'USD': return '$';
                        case 'TRY': return '₺';
                        default: return '';
                    }
                };
                const getCurrencyCodeFromSymbol = (symbol) => {
                    switch (symbol) {
                        case '£': return 'GBP';
                        case '€': return 'EUR';
                        case '$': return 'USD';
                        case '₺': return 'TRY';
                        default: return '';
                    }
                };
                const getExchangeSettingsLocal = () => {
                    try {
                        const ls = window.localStorage;
                        const enabled = ls.getItem('exchange_enabled') === '1';
                        const rateRaw = ls.getItem('exchange_rate');
                        const rate = rateRaw ? parseFloat(rateRaw) : 0;
                        const currency = ls.getItem('exchange_target_currency') || 'USD';
                        const label = ls.getItem('exchange_target_label') || currency;
                        // Track base symbol and reset if base currency changed
                        const baseSymbol = costBreakdown.currency_symbol || '';
                        const prevBaseSymbol = ls.getItem('exchange_base_symbol') || '';
                        if (prevBaseSymbol !== baseSymbol) {
                            ls.setItem('exchange_base_symbol', baseSymbol);
                            // Reset exchange settings on base currency change
                            ls.setItem('exchange_enabled', '0');
                            ls.removeItem('exchange_rate');
                            ls.setItem('exchange_target_currency', 'USD');
                            ls.removeItem('exchange_target_label');
                        }
                        return { enabled, rate: isNaN(rate) ? 0 : rate, currency, label };
                    } catch (_) {
                        return { enabled: false, rate: 0, currency: 'USD', label: 'USD' };
                    }
                };
                const formatCurrency = (amount) => {
                    const { enabled, rate, currency } = getExchangeSettingsLocal();
                    const numericAmount = parseFloat(amount);
                    const baseValue = isNaN(numericAmount) ? 0 : numericAmount;
                    if (enabled && rate > 0) {
                        const converted = baseValue * rate;
                        return `${currency} ${converted.toFixed(2)}`;
                    }
                    const baseSymbol = costBreakdown.currency_symbol || '';
                    const baseCode = getCurrencyCodeFromSymbol(baseSymbol);
                    return `${baseCode} ${baseSymbol}${baseValue.toFixed(2)}`;
                };


                // Helper to format date
                const formatDateDisplay = (dateStr) => {
                    if (!dateStr) return 'N/A';
                    try {
                        // Assuming dateStr is YYYY-MM-DD
                        const date = new Date(dateStr + 'T00:00:00');
                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        return date.toLocaleDateString('en-GB', options); // e.g., 15 Dec 2025
                    } catch (e) {
                        console.error("Error formatting date for display:", dateStr, e);
                        return 'Invalid Date';
                    }
                };

                // Helper to render item name with included badge
                // Use the shared Blade partial for the badge HTML to ensure consistency
                const includedBadgeHtml = '{!! str_replace(["\r", "\n"], "", view("admin.quotations._included_badge", ["item" => ["name" => "", "is_included" => true], "context" => "web"])->render()) !!}';

                const renderItemName = (item) => {
                    if (item.is_included) {
                        const name = item.name.replace(' (Included)', '');
                        return `${name}${includedBadgeHtml}`;
                    }
                    return item.name;
                };

                let html = `
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="bg-bayswater-blue text-white p-3">
                            <h3 class="font-semibold text-lg text-white">Your quote</h3>
                        </div>
                        <div class="p-4">`;

                // Location Header: Country, City, School/Centre
                const countryName = costBreakdown.country_name || 'Unknown Country';
                const cityName = costBreakdown.city_name || 'Unknown City';
                const schoolName = costBreakdown.school_name || 'Unknown School';
                html += `<div class="mb-4 text-sm text-gray-700">
                            <div><strong>Country:</strong> ${countryName}</div>
                            <div><strong>City:</strong> ${cityName}</div>
                            <div><strong>School/Centre:</strong> ${schoolName}</div>
                            <div id="breakdown-school-social-icons" class="flex flex-wrap gap-2 mt-1"></div>
                         </div>`;

                // Display Errors
                if (costBreakdown.errors && costBreakdown.errors.length > 0) {
                    html += `<div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Calculation Errors:</strong>
                                <ul class="list-disc list-inside">`;
                    costBreakdown.errors.forEach(error => {
                        html += `<li>${error}</li>`;
                    });
                    html += `</ul></div>`;
                }

                // Course Type Section
                if (costBreakdown.course_type_name) {
                    html += `<div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Course Type</h4>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">${costBreakdown.course_type_name}</span>
                                    <span class="text-sm text-gray-500">Selected</span>
                                </div>
                             </div>`;
                }

                // Course Section
                let courseTuitionItems = [];
                let secondCourseTuitionItems = [];
                let courseName = costBreakdown.course_name || 'Course details missing';
                let courseDuration = costBreakdown.course_duration_weeks || 'N/A';
                let secondCourseTuition = 0;
                let totalCourseTuition = 0;
                
                costBreakdown.items.forEach(item => {
                    if (item.category === 'tuition') {
                        courseTuitionItems.push(item);
                        totalCourseTuition += item.amount;
                    } else if (item.category === 'second_tuition') {
                        secondCourseTuitionItems.push(item);
                        secondCourseTuition += item.amount;
                        totalCourseTuition += item.amount;
                    }
                });
                
                // Calculate total course tuition for the first course
                let courseTuition = courseTuitionItems.reduce((sum, item) => sum + item.amount, 0);

                // Calculate proportional discounts for course tuition
                let firstCourseDiscounts = [];
                let secondCourseDiscounts = [];
                let otherDiscounts = [];
                
                if (costBreakdown.discounts && costBreakdown.discounts.length > 0) {
                    costBreakdown.discounts.forEach(discount => {
                        if ((discount.applied_to === 'course_tuition' || discount.applied_to === 'fixed_schedule_courses') && totalCourseTuition > 0) {
                            const primaryFlag = discount.applies_to_primary_course;
                            const secondFlag = discount.applies_to_second_course;

                            // If attribution flags indicate a specific course, assign full amount to that course
                            if (primaryFlag === true && secondFlag === false) {
                                firstCourseDiscounts.push({
                                    name: (discount.is_nationality || !discount.hidden) ? discount.name : '',
                                    amount: discount.amount
                                });
                            } else if (primaryFlag === false && secondFlag === true) {
                                secondCourseDiscounts.push({
                                    name: (discount.is_nationality || !discount.hidden) ? discount.name : '',
                                    amount: discount.amount
                                });
                            } else {
                                // Fall back to proportional split when flags are both true or both null (global tuition discount)
                                const firstCourseRatio = courseTuition / totalCourseTuition;
                                const secondCourseRatio = secondCourseTuition / totalCourseTuition;

                                if (courseTuition > 0) {
                                    const firstCourseDiscountAmount = discount.amount * firstCourseRatio;
                                    firstCourseDiscounts.push({
                                        name: (discount.is_nationality || !discount.hidden) ? discount.name : '',
                                        amount: firstCourseDiscountAmount
                                    });
                                }
                                if (secondCourseTuition > 0) {
                                    const secondCourseDiscountAmount = discount.amount * secondCourseRatio;
                                    secondCourseDiscounts.push({
                                        name: (discount.is_nationality || !discount.hidden) ? discount.name : '',
                                        amount: secondCourseDiscountAmount
                                    });
                                }
                            }
                        } else {
                            // Non-course tuition discounts go to other discounts section
                            otherDiscounts.push(discount);
                        }
                    });
                }

                // First Course Section
                html += `<div class="mb-6">
                            <h4 class="font-semibold text-bayswater-blue mb-2">${costBreakdown.second_course_name ? 'First Course' : 'Course'}</h4>`;
                
                // Display each course tuition item separately (for year-split display)
                courseTuitionItems.forEach(item => {
                    // Use course name from breakdown if available (to fix Junior package naming), preserving year if present
                    const yearMatch = item.name.match(/\(\d{4}\)/);
                    // Use regex to also capture "– 2025)" format if present, or just standard (2025)
                    const yearMatchExtended = item.name.match(/[\(–-]\s*20\d{2}\)?/);
                    const yearSuffix = yearMatchExtended ? ' ' + yearMatchExtended[0].trim() : '';
                    
                    const displayName = (costBreakdown.course_name && item.name !== costBreakdown.course_name) 
                        ? costBreakdown.course_name + yearSuffix
                        : item.name;

                    html += `<div class="flex justify-between items-center mb-1">
                                <span class="text-sm">${displayName}</span>
                                <span class="font-semibold">${formatCurrency(item.amount)}</span>
                             </div>`;
                });
                
                // Add first course discounts
                if (firstCourseDiscounts.length > 0) {
                    firstCourseDiscounts.forEach(discount => {
                        if (discount.amount > 0) {
                            html += `<div class="flex justify-between items-center mb-1 text-green-600">
                                        <span class="text-sm">${discount.name}</span>
                                        <span class="font-semibold">-${formatCurrency(discount.amount)}</span>
                                     </div>`;
                        }
                    });
                }
                
                html += `<div class="text-sm text-gray-600 mt-2">
                                <p><strong>Start date:</strong> ${formatDateDisplay(costBreakdown.course_start_date)}</p>
                                <p><strong>End date:</strong> ${formatDateDisplay(costBreakdown.course_end_date)}</p>
                                <p><strong>Duration:</strong> ${courseDuration} weeks</p>

                                ${costBreakdown.christmas_break && costBreakdown.christmas_break.has_break ? `
                                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                        <p class="text-sm font-medium text-blue-800 mb-1">
                                            <i class="fas fa-snowflake mr-1"></i>Christmas Break Notice
                                        </p>
                                        <p class="text-xs text-blue-700">
                                            ${costBreakdown.christmas_break.explanation}
                                        </p>
                                    </div>
                                ` : ''}
                            </div>
                            <div id="first-course-detail-buttons" class="flex flex-wrap gap-2 mt-2"></div>
                         </div>`;

                // Second Course Section (if exists)
                if (costBreakdown.second_course_name) {
                    html += `<div class="mb-6">
                                <h4 class="font-semibold text-green-700 mb-2">Second Course</h4>`;
                    
                    // Display each second course tuition item separately (for year-split display)
                    secondCourseTuitionItems.forEach(item => {
                        // Use course name from breakdown if available, preserving year if present
                        const yearMatchExtended = item.name.match(/[\(–-]\s*20\d{2}\)?/);
                        const yearSuffix = yearMatchExtended ? ' ' + yearMatchExtended[0].trim() : '';
                        
                        const displayName = (costBreakdown.second_course_name && item.name !== costBreakdown.second_course_name) 
                            ? costBreakdown.second_course_name + yearSuffix
                            : item.name;

                        html += `<div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">${displayName}</span>
                                    <span class="font-semibold">${formatCurrency(item.amount)}</span>
                                 </div>`;
                    });
                    
                    // Add second course discounts
                    if (secondCourseDiscounts.length > 0) {
                        secondCourseDiscounts.forEach(discount => {
                            if (discount.amount > 0) {
                                html += `<div class="flex justify-between items-center mb-1 text-green-600">
                                            <span class="text-sm">${discount.name}</span>
                                            <span class="font-semibold">-${formatCurrency(discount.amount)}</span>
                                         </div>`;
                            }
                        });
                    }
                    
                    html += `<div class="text-sm text-gray-600 mt-2">
                                    <p><strong>Start date:</strong> ${formatDateDisplay(costBreakdown.second_course_start_date)}</p>
                                    <p><strong>End date:</strong> ${formatDateDisplay(costBreakdown.second_course_end_date)}</p>
                                    <p><strong>Duration:</strong> ${costBreakdown.second_course_duration_weeks || 'N/A'} weeks</p>

                                    ${costBreakdown.second_course_christmas_break && costBreakdown.second_course_christmas_break.has_break ? `
                                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-md">
                                            <p class="text-sm font-medium text-blue-800 mb-1">
                                                <i class="fas fa-snowflake mr-1"></i>Christmas Break Notice
                                            </p>
                                            <p class="text-xs text-blue-700">
                                                ${costBreakdown.second_course_christmas_break.explanation}
                                            </p>
                                        </div>
                                    ` : ''}
                                </div>
                             </div>`;
                }

                // Accommodation Section
                let accommodationTotal = (costBreakdown.subtotals?.accommodation || 0) + (costBreakdown.subtotals?.second_accommodation || 0);
                let firstAccommodationItems = [];
                let secondAccommodationItems = [];
                
                costBreakdown.items.forEach(item => {
                    if (item.category === 'accommodation') {
                        firstAccommodationItems.push(item);
                    } else if (item.category === 'second_accommodation') {
                        secondAccommodationItems.push(item);
                    }
                });

                // First Accommodation Section
                if (firstAccommodationItems.length > 0 || (costBreakdown.accommodation_start_date && costBreakdown.accommodation_end_date)) {
                    html += `<div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Accommodation</h4>`;
                    
                    // Display each first accommodation item
                    firstAccommodationItems.forEach(item => {
                        html += `<div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">${renderItemName(item)}</span>
                                    <span class="font-semibold">${formatCurrency(item.amount)}</span>
                                 </div>`;
                    });
                    
                    // Display accommodation details if available
                    if (costBreakdown.accommodation_start_date && costBreakdown.accommodation_end_date) {
                        html += `<div class="text-sm text-gray-600 mt-2">
                                    <p><strong>Start date:</strong> ${formatDateDisplay(costBreakdown.accommodation_start_date)}</p>
                                    <p><strong>End date:</strong> ${formatDateDisplay(costBreakdown.accommodation_end_date)}</p>
                                    <p><strong>Duration:</strong> ${costBreakdown.accommodation_duration_weeks || 'N/A'} weeks</p>
                                 </div>`;
                    }
                    
                    html += `</div>`;
                }

                // Second Accommodation Section
                if (secondAccommodationItems.length > 0 || (costBreakdown.second_accommodation_start_date && costBreakdown.second_accommodation_end_date)) {
                    html += `<div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Second Accommodation</h4>`;
                    
                    // Display each second accommodation item
                    secondAccommodationItems.forEach(item => {
                        html += `<div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">${renderItemName(item)}</span>
                                    <span class="font-semibold">${formatCurrency(item.amount)}</span>
                                 </div>`;
                    });
                    
                    // Display second accommodation details if available
                    if (costBreakdown.second_accommodation_start_date && costBreakdown.second_accommodation_end_date) {
                        html += `<div class="text-sm text-gray-600 mt-2">
                                    <p><strong>Start date:</strong> ${formatDateDisplay(costBreakdown.second_accommodation_start_date)}</p>
                                    <p><strong>End date:</strong> ${formatDateDisplay(costBreakdown.second_accommodation_end_date)}</p>
                                    <p><strong>Duration:</strong> ${costBreakdown.second_accommodation_duration_weeks || 'N/A'} weeks</p>
                                 </div>`;
                    }
                    
                    html += `</div>`;
                }

                // Sub Total (Course + Accommodation)
                // Calculate total discounts for course tuition to subtract from subtotal
                let totalFirstCourseDiscount = firstCourseDiscounts.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
                let totalSecondCourseDiscount = secondCourseDiscounts.reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
                
                let subTotalCourseAccom = (costBreakdown.subtotals?.tuition || 0) + (costBreakdown.subtotals?.second_tuition || 0) + (costBreakdown.subtotals?.accommodation || 0) + (costBreakdown.subtotals?.second_accommodation || 0) - totalFirstCourseDiscount - totalSecondCourseDiscount;
                html += `<div class="py-3 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold">Sub Total</span>
                                <span class="font-semibold">${formatCurrency(subTotalCourseAccom)}</span>
                            </div>
                         </div>`;

                // Optional Extras Section
                let feesTotal = costBreakdown.subtotals?.fees || 0;
                let addonsTotal = costBreakdown.subtotals?.addons || 0;
                let extrasExist = false;
                let extrasHtml = '';

                console.log('Processing items for Optional Extras...');
                costBreakdown.items.forEach((item, index) => {
                    console.log(`Item ${index}: Name='${item.name}', Category='${item.category}', Amount=${item.amount}`); // Log each item
                    if (item.category === 'fees' || item.category === 'addons') {
                        extrasExist = true;
                        extrasHtml += `<div class="flex justify-between items-center mb-1">
                                        <span class="text-sm">${renderItemName(item)}</span>
                                        <span class="font-semibold">${formatCurrency(item.amount)}</span>
                                     </div>`;
                        if (item.name.includes('Extra Christmas Accommodation')) { // Specific check
                            console.log('FOUND Extra Christmas Accommodation item in loop.');
                        }
                    }
                });
                 console.log('Finished processing items for Optional Extras. extrasExist:', extrasExist);

                if (extrasExist) {
                    html += `<div class="mt-6 mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Optional extras</h4>
                                ${extrasHtml}
                             </div>`;
                    // Sub Total (Fees + Addons)
                    html += `<div class="py-3 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold">Sub Total</span>
                                    <span class="font-semibold">${formatCurrency(feesTotal + addonsTotal)}</span>
                                </div>
                             </div>`;
                }

                // Other Discounts Section (non-course tuition discounts)
                if (otherDiscounts.length > 0) {
                    html += `<div class="mt-6 mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Other Discounts Applied</h4>`;
                    otherDiscounts.forEach(discount => {
                        // Only display if amount > 0 (waivers are handled implicitly by fee not being added or shown)
                        if (discount.amount > 0) {
                             html += `<div class="flex justify-between items-center mb-1 text-green-600">
                                         <span class="text-sm">${(discount.is_nationality || !discount.hidden) ? discount.name : ''}</span>
                                         <span class="font-semibold">-${formatCurrency(discount.amount)}</span>
                                      </div>`;
                        }
                    });
                    html += `</div>`;
                }

                // Notes Section
                if (costBreakdown.notes && costBreakdown.notes.length > 0) {
                    html += `<div class="mt-6 mb-6"style="display: none;">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Notes</h4>
                                <ul class="list-disc list-inside text-sm text-gray-600">`;
                    costBreakdown.notes.forEach(note => {
                        html += `<li>${note}</li>`;
                    });
                    html += `</ul></div>`;
                }

                // Total Section
                console.log('Rendering Total. Value from costBreakdown.total:', costBreakdown.total); // Log total value
                html += `<div class="mt-6 py-4 bg-bayswater-blue text-white px-4 -mx-4">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-lg">Total:</span>
                                <span class="font-bold text-lg">${formatCurrency(costBreakdown.total)}</span>
                            </div>
                         </div>`;
                // Exchange UI block under Total
                (() => {
                    const ex = getExchangeSettingsLocal();
                    const convertedTotal = (ex.enabled && ex.rate > 0) ? ((parseFloat(costBreakdown.total) || 0) * ex.rate) : null;
                    const symbol = ex.enabled ? getCurrencySymbolLocal(ex.currency) : '';
                    html += `<div class="mt-2 px-4 -mx-4">
                                <div class="bg-blue-50 rounded p-3" style="display: none;">
                                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
                                        <label class="text-sm font-semibold text-bayswater-blue w-full sm:w-auto mb-2 sm:mb-0">Exchange Rate</label>
                                        <select id="exchange-target-currency" class="border rounded px-2 py-1 text-sm w-full sm:w-auto max-w-[250px] sm:mr-3 mb-2 sm:mb-0">
                                            @if(isset($exchangeNames) && count($exchangeNames))
                                                <option value="">{{ __('Select currency') }}</option>
                                                @foreach($exchangeNames as $ex)
                                                    <option value="{{ strtoupper($ex->name) }}">{{ $ex->label ?? strtoupper($ex->name) }}</option>
                                                @endforeach
                                            @else
                                                <option value="">{{ __('No exchange options configured') }}</option>
                                            @endif
                                        </select>
                                        <input id="exchange-rate" type="text" inputmode="decimal" pattern="^[0-9]*[.,]?[0-9]{0,4}$" class="border rounded px-2 py-1 text-sm w-full sm:w-auto max-w-[250px] sm:mr-3 mb-2 sm:mb-0" placeholder="Rate">
                                        <label class="flex items-center gap-2 text-sm w-full sm:w-auto mb-2 sm:mb-0">
                                            <input id="exchange-enabled" type="checkbox"> Show converted
                                        </label>
                                    </div>
                                    <div class="text-xs mt-2 text-gray-700">
                                        ${ex.enabled && convertedTotal !== null
                                            ? 'Converted Total (' + ex.currency + ' @ ' + ex.rate + '): <strong>' + ex.currency + ' ' + convertedTotal.toFixed(2) + '</strong>'
                                            : 'Set currency and rate to display converted values.'}
                                    </div>
                                </div>
                            </div>`;
                })();

                // Action Buttons
                html += `<div class="mt-4 flex justify-end space-x-4 pb-4">
                            <button type="button" id="print-quote" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Quote
                            </button>
                            <button type="button" id="download-pdf" class="inline-flex items-center px-4 py-2 bg-bayswater-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bayswater-blue-dark focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Download PDF
                            </button>
                         </div>`;

                html += `</div></div>`; // Close p-4 and main div

                // Update the results container
                resultsContainer.innerHTML = html;

                // Update social icons in the breakdown immediately after render
                const currentSchoolId = document.getElementById('school_id').value;
                if (currentSchoolId) {
                    updateSchoolSocials(currentSchoolId);
                }

                // Update Factsheet buttons in the breakdown
                if (typeof updateBreakdownFactsheets === 'function') {
                    updateBreakdownFactsheets();
                }

                // Initialize Exchange UI events
                (function initExchangeUI(){
                    const currencyEl = document.getElementById('exchange-target-currency');
                    const rateEl = document.getElementById('exchange-rate');
                    const enabledEl = document.getElementById('exchange-enabled');
                    if (!currencyEl || !rateEl || !enabledEl) {
                        console.log('Exchange UI elements not found after render');
                        return;
                    }
                    try {
                        const ls = window.localStorage;
                        let curr = ls.getItem('exchange_target_currency') || 'USD';
                        const opts = Array.from(currencyEl.options).map(o => o.value);
                        if (!opts.includes(curr)) curr = opts[0] || 'USD';
                        currencyEl.value = curr;
                        // Persist label for selected option
                        const initLabel = currencyEl.options[currencyEl.selectedIndex]?.text || curr;
                        ls.setItem('exchange_target_label', initLabel);
                        const rate = ls.getItem('exchange_rate');
                        if (rate) {
                            // Normalize any stored commas or invalid characters on load and persist
                            let s = String(rate).replace(/,/g, '.').replace(/[^0-9.]/g, '');
                            const firstDot = s.indexOf('.');
                            if (firstDot !== -1) {
                                const intPart = s.slice(0, firstDot).replace(/\./g, '');
                                let fracPart = s.slice(firstDot + 1).replace(/\./g, '');
                                fracPart = fracPart.slice(0, 4);
                                s = intPart + (fracPart.length ? '.' + fracPart : '.');
                                if (s === '.') s = '';
                            } else {
                                s = s.replace(/\./g, '');
                            }
                            rateEl.value = s;
                            if (s === '') {
                                ls.removeItem('exchange_rate');
                            } else {
                                ls.setItem('exchange_rate', s);
                            }
                        } else {
                            rateEl.value = '';
                        }
                        enabledEl.checked = ls.getItem('exchange_enabled') === '1';

                        const triggerRecalc = () => { try { autoCalculate(); } catch(e) { console.warn('autoCalculate not available:', e); } };

                        // Normalization helper: accept '.' and ',', keep only digits and a single decimal separator, max 4 decimals
                        const normalizeRate = (raw) => {
                            if (typeof raw !== 'string') raw = String(raw ?? '');
                            let s = raw.replace(/,/g, '.');
                            // Remove invalid characters
                            s = s.replace(/[^0-9.]/g, '');
                            // Ensure only one decimal point
                            const firstDot = s.indexOf('.');
                            if (firstDot !== -1) {
                                const intPart = s.slice(0, firstDot).replace(/\./g, '');
                                let fracPart = s.slice(firstDot + 1).replace(/\./g, '');
                                // Limit to 4 decimal places (no rounding on input)
                                fracPart = fracPart.slice(0, 4);
                                s = intPart + (fracPart.length ? '.' + fracPart : '.');
                                // Avoid solitary '.' (convert to empty)
                                if (s === '.') s = '';
                            } else {
                                // No dot, remove any stray dots
                                s = s.replace(/\./g, '');
                            }
                            return s;
                        };

                        currencyEl.addEventListener('change', () => {
                            ls.setItem('exchange_target_currency', currencyEl.value);
                            const selectedLabel = currencyEl.options[currencyEl.selectedIndex]?.text || currencyEl.value;
                            ls.setItem('exchange_target_label', selectedLabel);
                            if (enabledEl.checked) {
                                triggerRecalc();
                            }
                        });
                        rateEl.addEventListener('input', () => {
                            const normalized = normalizeRate(rateEl.value.trim());
                            // Reflect normalized value back in the field
                            rateEl.value = normalized;
                            if (normalized === '') {
                                ls.removeItem('exchange_rate');
                            } else {
                                ls.setItem('exchange_rate', normalized);
                            }
                            // Do NOT recalc while typing; wait for blur
                        });
                        rateEl.addEventListener('blur', () => {
                            const normalized = normalizeRate(rateEl.value.trim());
                            rateEl.value = normalized;
                            if (normalized === '') {
                                ls.removeItem('exchange_rate');
                            } else {
                                ls.setItem('exchange_rate', normalized);
                            }
                            if (enabledEl.checked) {
                                triggerRecalc();
                            }
                        });
                        // Trigger recalculation when pressing Enter inside the rate field
                        rateEl.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter') {
                                e.preventDefault();
                                const normalized = normalizeRate(rateEl.value.trim());
                                rateEl.value = normalized;
                                if (normalized === '') {
                                    ls.removeItem('exchange_rate');
                                } else {
                                    ls.setItem('exchange_rate', normalized);
                                }
                                if (enabledEl.checked) {
                                    triggerRecalc();
                                }
                                // Optional: blur to match onBlur UX
                                rateEl.blur();
                            }
                        });
                        enabledEl.addEventListener('change', () => {
                            ls.setItem('exchange_enabled', enabledEl.checked ? '1' : '0');
                            triggerRecalc();
                        });
                    } catch (e) {
                        console.warn('Failed to initialize Exchange UI', e);
                    }
                }());

                // Re-initialize buttons after updating HTML
                initializePrintAndPdfButtons();
            }


            // --- Dual Course Functionality ---
            const addCourseBtn = document.getElementById('add-course-btn');
            const removeCourseBtn = document.getElementById('remove-course-btn');
            const secondCourseSection = document.getElementById('second-course-section');
            
            // Second course form elements (variables declared earlier)
            
            let isSecondCourseVisible = false;
            
            // --- Dual Accommodation Functionality ---
            const addAccommodationBtn = document.getElementById('add-accommodation-btn');
            const removeAccommodationBtn = document.getElementById('remove-accommodation-btn');
            const secondAccommodationSection = document.getElementById('second-accommodation-section');
            const secondAccommodationSelect = document.getElementById('second_accommodation_id');
            const secondAccommodationDurationSelect = document.getElementById('second_accommodation_duration_weeks');
            const secondPrivateBathroomDiv = document.getElementById('second_private_bathroom_div');
            const secondPrivateBathroomCheckbox = document.getElementById('second_private_bathroom');
            const secondDietarySupplementDiv = document.getElementById('second_dietary_supplement_div');
            const secondDietarySupplementCheckbox = document.getElementById('second_dietary_supplement');
            const secondChristmasAccommodationDiv = document.getElementById('second_christmas_accommodation_div');
            const secondChristmasAccommodationSelect = document.getElementById('second_christmas_accommodation');
            const secondChristmasPeriodInfo = document.getElementById('second_christmas_period_info');
            const secondChristmasExtraWeeksDiv = document.getElementById('second_christmas_extra_weeks_div');
            const secondChristmasExtraWeeksSelect = document.getElementById('second_christmas_extra_weeks');
            
            let isSecondAccommodationVisible = false;
            
            // Function to enable/disable Add Course button based on first course start date
            function updateAddCourseButtonState() {
                const buttonMessage = addCourseBtn.parentElement.querySelector('p');
                if (startDateInput.value && !isSecondCourseVisible) {
                    addCourseBtn.disabled = false;
                    if (buttonMessage) {
                        buttonMessage.textContent = 'Click to add a second course';
                    }
                } else {
                    addCourseBtn.disabled = true;
                    if (buttonMessage) {
                        if (!startDateInput.value) {
                            buttonMessage.textContent = 'Select a start date for the first course to enable this option';
                        } else if (isSecondCourseVisible) {
                            buttonMessage.textContent = 'Second course is already added';
                        }
                    }
                }
            }
            
            // Function to synchronize shared data between courses
            function synchronizeSharedData() {
                if (isSecondCourseVisible) {
                    // Sync Region - filter second course cities based on first course country
                    const selectedCountry = countrySelect.value;
                    if (selectedCountry) {
                        // Enable second city dropdown and filter by country
                        secondCitySelect.disabled = false;
                        filterOptions(secondCitySelect, 'data-country', selectedCountry);
                    } else {
                        secondCitySelect.disabled = true;
                        secondCitySelect.value = '';
                    }
                    
                    // Sync City - copy selected city from first course to second course
                    const selectedCity = citySelect.value;
                    secondCitySelect.value = selectedCity;
                    secondCitySelect.disabled = true; // Make second course city non-editable
                    
                    // Sync School - copy selected school from first course to second course
                    const selectedSchool = schoolSelect.value;
                    secondSchoolSelect.value = selectedSchool;
                    secondSchoolSelect.disabled = true; // Make second course school non-editable
                    
                    // If school is selected, filter second course options based on the selected school
                    if (selectedSchool) {
                        // Show only courses that match the selected school in second course
                        for (let i = 0; i < secondCourseSelect.options.length; i++) {
                            const option = secondCourseSelect.options[i];
                            if (option.value === "") continue; // Skip the default option
                            
                            const schoolId = option.getAttribute('data-school');
                            option.style.display = (String(schoolId) === String(selectedSchool)) ? '' : 'none';
                        }
                        
                        // Enable second course type and course dropdowns
                        secondCourseTypeSelect.disabled = false;
                        secondCourseSelect.disabled = false;
                        
                        // Show all course types in second course
                        for (let i = 0; i < secondCourseTypeSelect.options.length; i++) {
                            const option = secondCourseTypeSelect.options[i];
                            if (option.value === "") continue; // Skip the default option
                            option.style.display = '';
                        }
                    } else {
                        // Disable and reset second course dropdowns if no school selected
                        secondCourseTypeSelect.disabled = true;
                        secondCourseSelect.disabled = true;
                        secondCourseTypeSelect.value = '';
                        secondCourseSelect.value = '';
                    }
                }
            }
            
            // Function to show second course section
            function showSecondCourse() {
                secondCourseSection.style.display = 'block';
                isSecondCourseVisible = true;
                updateAddCourseButtonState();
                synchronizeSharedData();
                updateSecondCourseMinDate(); // Apply date restrictions immediately
                
                // Ensure second course duration dropdown is populated if a course is already selected
                if (secondCourseSelect.value) {
                    updateSecondCourseMaxWeeks();
                }
                
                // Scroll to second course section
                secondCourseSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            // Function to hide second course section
            function hideSecondCourse() {
                secondCourseSection.style.display = 'none';
                isSecondCourseVisible = false;
                
                // Reset all second course fields
                secondCitySelect.value = '';
                secondSchoolSelect.value = '';
                secondCourseTypeSelect.value = '';
                secondCourseSelect.value = '';
                secondStartDateInput.value = '';
                secondCourseDurationSelect.value = '';
                
                // Disable all second course fields
                secondCitySelect.disabled = true;
                secondSchoolSelect.disabled = true;
                secondCourseTypeSelect.disabled = true;
                secondCourseSelect.disabled = true;
                secondStartDateInput.disabled = true;
                secondCourseDurationSelect.disabled = true;
                
                // Hide error message
                secondStartDateError.style.display = 'none';
                
                updateAddCourseButtonState();
                
                // Update first accommodation duration when second course is removed
                if (accommodationSelect.value) {
                    populateAccommodationWeeks();
                }
                
                // Trigger recalculation to remove second course from totals
                autoCalculate();
            }
            
            // Function to validate Monday start date for second course
            function validateSecondCourseStartDate() {
                if (secondStartDateInput.value) {
                    const selectedDate = new Date(secondStartDateInput.value + 'T00:00:00');
                    const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 1 = Monday, etc.
                    
                    if (dayOfWeek !== 1) { // Not Monday
                        secondStartDateError.style.display = 'block';
                        secondStartDateInput.setCustomValidity('Start date must be a Monday.');
                        return false;
                    } else {
                        secondStartDateError.style.display = 'none';
                        secondStartDateInput.setCustomValidity('');
                        return true;
                    }
                }
                return true;
            }
            
            // Function to calculate first course end date (Friday), accounting for Christmas overlap
            function calculateFirstCourseEndDate() {
                if (!startDateInput.value || !courseDurationSelect.value) {
                    return null;
                }

                const startDate = new Date(startDateInput.value + 'T00:00:00');
                const courseWeeks = parseInt(courseDurationSelect.value);

                // Ensure start date is Monday (adjust if necessary)
                let courseStart = new Date(startDate);
                if (courseStart.getDay() !== 1) { // Not Monday
                    // Move to next Monday
                    const daysToAdd = (8 - courseStart.getDay()) % 7;
                    courseStart.setDate(courseStart.getDate() + daysToAdd);
                }

                // Base end date (Friday of final week)
                let baseEnd = new Date(courseStart);
                baseEnd.setDate(baseEnd.getDate() + (courseWeeks - 1) * 7 + 4); // +4 to get to Friday

                // If school has Christmas period configured, extend end date for overlapping weeks
                let extendedWeeks = 0;
                if (schoolChristmasStartDate && schoolChristmasEndDate) {
                    const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                    const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');

                    // Iterate each instructional week and count overlap with Christmas period
                    for (let i = 0; i < courseWeeks; i++) {
                        const weekStart = new Date(courseStart);
                        weekStart.setDate(weekStart.getDate() + i * 7);
                        const weekEndFriday = new Date(weekStart);
                        weekEndFriday.setDate(weekStart.getDate() + 4);

                        // Overlap if weekStart <= christmasEnd && weekEndFriday >= christmasStart
                        if (weekStart <= christmasEnd && weekEndFriday >= christmasStart) {
                            extendedWeeks += 1;
                        }
                    }
                }

                // Actual end date: add any extended weeks caused by Christmas overlap
                const courseEnd = new Date(courseStart);
                courseEnd.setDate(courseEnd.getDate() + ((courseWeeks - 1) + extendedWeeks) * 7 + 4);

                return courseEnd;
            }
            
            // Function to find the first Monday after a given date
            function getNextMonday(date) {
                const nextMonday = new Date(date);
                nextMonday.setDate(nextMonday.getDate() + 1); // Start from the day after
                
                // Find the next Monday
                const daysToAdd = (8 - nextMonday.getDay()) % 7;
                if (daysToAdd === 0 && nextMonday.getDay() !== 1) {
                    nextMonday.setDate(nextMonday.getDate() + 7); // If it's already Monday, go to next Monday
                } else {
                    nextMonday.setDate(nextMonday.getDate() + daysToAdd);
                }
                
                return nextMonday;
            }
            
            // Function to update second course minimum date based on first course and Christmas breaks
            function updateSecondCourseMinDate() {
                const firstCourseEndDate = calculateFirstCourseEndDate();
                
                if (firstCourseEndDate) {
                    let minSecondCourseDate = getNextMonday(firstCourseEndDate);
                    
                    // Check if minimum date falls within Christmas break and adjust if necessary
                    if (schoolChristmasStartDate && schoolChristmasEndDate) {
                        const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                        const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');
                        
                        // If the calculated minimum date falls within Christmas break,
                        // move it to the first Monday after Christmas break
                        if (minSecondCourseDate >= christmasStart && minSecondCourseDate <= christmasEnd) {
                            minSecondCourseDate = getNextMonday(christmasEnd);
                            // If the first course overlapped Christmas (extended), skip the partial week and go to the next Monday
                            const baseEndCheck = new Date(firstCourseEndDate);
                            // If base end (without extension) would have fallen within the Christmas break,
                            // ensure we start the second course on the Monday after the New Year week
                            if (baseEndCheck >= christmasStart && baseEndCheck <= christmasEnd) {
                                minSecondCourseDate.setDate(minSecondCourseDate.getDate() + 7);
                            }
                        }
                    }
                    
                    const minDateStr = minSecondCourseDate.toISOString().split('T')[0];
                    
                    // Update the flatpickr instance for second course
                    if (secondStartDateInput._flatpickr) {
                        secondStartDateInput._flatpickr.set('minDate', minDateStr);
                    }
                    
                    // Clear second course start date if it's now invalid
                    if (secondStartDateInput.value) {
                        const currentSecondDate = new Date(secondStartDateInput.value + 'T00:00:00');
                        if (currentSecondDate < minSecondCourseDate) {
                            secondStartDateInput.value = '';
                            secondCourseDurationSelect.value = '';
                            secondCourseDurationSelect.disabled = true;
                        }
                    }
                } else {
                    // Reset to default minimum date if first course data is incomplete
                    const currentYear = new Date().getFullYear();
                    if (secondStartDateInput._flatpickr) {
                        secondStartDateInput._flatpickr.set('minDate', `${currentYear}-01-01`);
                    }
                }
            }
            
            // Function to update second course duration options
            function updateSecondCourseMaxWeeks() {
                console.log('updateSecondCourseMaxWeeks called');
                
                // Reset duration dropdown to default state
                secondCourseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';
                secondCourseDurationSelect.disabled = true;
                
                if (!secondCourseSelect.value) {
                    console.log('No second course selected');
                    return;
                }
                
                console.log('Second course value:', secondCourseSelect.value);
                const selectedOption = secondCourseSelect.options[secondCourseSelect.selectedIndex];
                
                if (!selectedOption) {
                    console.error('Selected option not found for second course');
                    return;
                }
                
                const schoolId = selectedOption.getAttribute('data-school');
                const pricingType = selectedOption.getAttribute('data-pricing-type');
                console.log('School ID:', schoolId, 'Pricing type:', pricingType);

                // If second course is fixed schedule, restrict start dates and durations based on schedules
                if (pricingType === 'fixed_schedule') {
                    const secondCourseId = secondCourseSelect.value;
                    
                    // Check if allCourseSchedules is available and has data for this course
                    if (!allCourseSchedules || !allCourseSchedules[secondCourseId]) {
                        console.warn('No schedule data available for course ID:', secondCourseId);
                        // Fallback to regular course behavior
                        handleRegularCourse(schoolId);
                        return;
                    }
                    
                    const schedules = allCourseSchedules[secondCourseId];
                    
                    if (!Array.isArray(schedules) || schedules.length === 0) {
                        console.warn('Empty or invalid schedules for course ID:', secondCourseId);
                        // Fallback to regular course behavior
                        handleRegularCourse(schoolId);
                        return;
                    }
                    
                    // Build allowed dates list from schedules - ensure Monday restriction takes precedence
                    let allowedDates = schedules
                        .map(s => s.start_date)
                        .filter(date => {
                            if (!date) return false;
                            // Ensure all dates are Mondays (day 1 in JavaScript Date)
                            // Handle both ISO timestamps and simple date strings
                            const dateObj = new Date(date);
                            return dateObj.getDay() === 1; // Monday check takes precedence
                        })
                        // Normalize to 'YYYY-MM-DD' to match input value/flatpickr format
                        .map(date => {
                            const d = new Date(date);
                            const yyyy = d.getFullYear();
                            const mm = String(d.getMonth() + 1).padStart(2, '0');
                            const dd = String(d.getDate()).padStart(2, '0');
                            return `${yyyy}-${mm}-${dd}`;
                        });
                    
                    // Exclude Christmas period
                    if (schoolChristmasStartDate && schoolChristmasEndDate) {
                        const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                        const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');
                        allowedDates = allowedDates.filter(d => {
                            const dt = new Date(d);
                            return !(dt >= christmasStart && dt <= christmasEnd);
                        });
                    }
                    
                    // Respect current minDate set based on first course end
                    let minStr = null;
                    try {
                        if (secondStartDateInput && 
                            secondStartDateInput._flatpickr && 
                            secondStartDateInput._flatpickr.config && 
                            secondStartDateInput._flatpickr.config.minDate) {
                            
                            const minCfg = secondStartDateInput._flatpickr.config.minDate;
                            if (typeof minCfg === 'string') {
                                minStr = minCfg;
                            } else if (minCfg instanceof Date && minCfg !== null) {
                                minStr = minCfg.toISOString().split('T')[0];
                            }
                        }
                    } catch (error) {
                        console.warn('Error accessing flatpickr minDate config:', error);
                    }
                    
                    if (minStr) {
                        const minDateObj = new Date(minStr + 'T00:00:00');
                        allowedDates = allowedDates.filter(d => new Date(d + 'T00:00:00') >= minDateObj);
                    }
                    
                    // Update date picker with allowed dates
                    if (secondStartDateInput && secondStartDateInput._flatpickr) {
                        try {
                            // Whitelist only normalized allowed dates
                            secondStartDateInput._flatpickr.set('enable', allowedDates);
                            // Clear invalid selection when not part of normalized allowed dates
                            if (secondStartDateInput.value && !allowedDates.includes(secondStartDateInput.value)) {
                                secondStartDateInput.value = '';
                            }
                        } catch (error) {
                            console.error('Error updating secondStartDatePicker:', error);
                        }
                    }

                    // Populate second course durations from schedules
                    const validSchedules = schedules.filter(s => s.duration_weeks && !isNaN(parseInt(s.duration_weeks, 10)));
                    
                    if (validSchedules.length === 0) {
                        console.warn('No valid duration schedules found for course ID:', secondCourseId);
                        // Fallback to regular course behavior
                        handleRegularCourse(schoolId);
                        return;
                    }
                    
                    const uniqueDurations = Array.from(new Set(validSchedules.map(s => parseInt(s.duration_weeks, 10)))).sort((a,b) => a - b);
                    
                    // Populate duration options
                    uniqueDurations.forEach(weeks => {
                        const option = document.createElement('option');
                        option.value = weeks;
                        option.textContent = `${weeks} week${weeks > 1 ? 's' : ''}`;
                        secondCourseDurationSelect.appendChild(option);
                    });
                    
                    // If a start date is selected and matches a schedule, lock duration
                    if (secondStartDateInput.value) {
                        const schedForDate = schedules.find(s => s.start_date === secondStartDateInput.value);
                        if (schedForDate && schedForDate.duration_weeks) {
                            secondCourseDurationSelect.value = String(parseInt(schedForDate.duration_weeks, 10));
                            secondCourseDurationSelect.disabled = true;
                        } else {
                            secondCourseDurationSelect.disabled = false;
                        }
                    } else {
                        secondCourseDurationSelect.disabled = false;
                        if (uniqueDurations.length > 0) {
                            secondCourseDurationSelect.selectedIndex = 1;
                            secondCourseDurationSelect.dispatchEvent(new Event('change'));
                        }
                    }
                    return; // Done for fixed schedule
                }
                
                // Handle regular courses
                handleRegularCourse(schoolId);
            }
            
            // Helper function to handle regular (non-fixed schedule) courses
            function handleRegularCourse(schoolId) {
                // Find the school in the schools data
                const school = @json($schools).find(s => s.id == schoolId);
                if (!school) {
                    console.error('School not found for ID:', schoolId);
                    return;
                }
                
                const maxWeeks = school.max_weeks || 52;
                
                // Reset date restrictions to defaults (non-fixed schedule)
                // This ensures Monday restrictions are properly applied
                if (secondStartDateInput && secondStartDateInput._flatpickr) {
                    try {
                        // Unset whitelist so default disable rules (Mondays and Christmas) apply
                        secondStartDateInput._flatpickr.set('enable', null);
                    } catch (error) {
                        console.error('Error resetting secondStartDatePicker:', error);
                    }
                }

                // Clear existing options and add week options
                secondCourseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';
                
                for (let i = 1; i <= maxWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} week${i > 1 ? 's' : ''}`;
                    secondCourseDurationSelect.appendChild(option);
                }
                
                secondCourseDurationSelect.disabled = false;
                
                // Auto-select the first week option (same as first course behavior)
                if (secondCourseDurationSelect.options.length > 1) {
                    secondCourseDurationSelect.selectedIndex = 1; // Select the first week option
                    // Trigger change event to update calculations
                    secondCourseDurationSelect.dispatchEvent(new Event('change'));
                }
            }
            
            // Event listeners for Add Course button
            addCourseBtn.addEventListener('click', function() {
                if (!addCourseBtn.disabled) {
                    showSecondCourse();
                }
            });
            
            // Event listeners for Remove Course button
            removeCourseBtn.addEventListener('click', function() {
                hideSecondCourse();
            });
            
            // Event listener for first course start date to enable/disable Add Course button
            startDateInput.addEventListener('change', function() {
                updateAddCourseButtonState();
                updateSecondCourseMinDate(); // Update second course date restrictions
            });
            
            // Event listener for first course duration to update second course date restrictions
            courseDurationSelect.addEventListener('change', function() {
                updateSecondCourseMinDate();
            });
            
            // Event listeners for shared data synchronization
            countrySelect.addEventListener('change', function() {
                synchronizeSharedData();
            });
            
            // Event listeners for second course dropdowns (similar to first course)
            secondCitySelect.addEventListener('change', function() {
                if (this.value) {
                    secondSchoolSelect.disabled = false;
                    filterOptions(secondSchoolSelect, 'data-city', this.value);
                } else {
                    secondSchoolSelect.disabled = true;
                    secondSchoolSelect.value = '';
                    secondCourseTypeSelect.disabled = true;
                    secondCourseTypeSelect.value = '';
                    secondCourseSelect.disabled = true;
                    secondCourseSelect.value = '';
                    secondStartDateInput.disabled = true;
                    secondStartDateInput.value = '';
                    secondCourseDurationSelect.disabled = true;
                    secondCourseDurationSelect.value = '';
                }
            });
            
            secondSchoolSelect.addEventListener('change', function() {
                if (this.value) {
                    secondCourseTypeSelect.disabled = false;
                    secondCourseSelect.disabled = false;
                    
                    // Filter courses by school
                    filterOptions(secondCourseSelect, 'data-school', this.value);
                } else {
                    secondCourseTypeSelect.disabled = true;
                    secondCourseTypeSelect.value = '';
                    secondCourseSelect.disabled = true;
                    secondCourseSelect.value = '';
                    secondStartDateInput.disabled = true;
                    secondStartDateInput.value = '';
                    secondCourseDurationSelect.disabled = true;
                    secondCourseDurationSelect.value = '';
                }
                // Filter second accommodations based on new school
                filterSecondAccommodations();
            });
            
            secondCourseTypeSelect.addEventListener('change', function() {
                if (this.value && secondSchoolSelect.value) {
                    const schoolId = secondSchoolSelect.value;
                    const courseTypeId = this.value;
                    
                    // iOS-compatible: rebuild courses filtered by school AND course type
                    filterOptions(secondCourseSelect, ['data-school', 'data-course-type'], [schoolId, courseTypeId]);
                    
                    // Reset course selection if current selection no longer exists after rebuild
                    if (secondCourseSelect.value) {
                        const exists = Array.from(secondCourseSelect.options).some(opt => opt.value === secondCourseSelect.value);
                        if (!exists) {
                            secondCourseSelect.value = '';
                        }
                    }
                } else {
                    // Clear second course list when not applicable
                    filterOptions(secondCourseSelect, 'data-school', null);
                    secondCourseSelect.disabled = true;
                }
                // Filter second accommodations based on new course type
                filterSecondAccommodations();
            });
            
            secondCourseSelect.addEventListener('change', function() {
                console.log('Second course select changed:', this.value);
                if (this.value) {
                    secondStartDateInput.disabled = false;
                    console.log('Calling updateSecondCourseMaxWeeks...');
                    updateSecondCourseMaxWeeks();
                } else {
                    secondStartDateInput.disabled = true;
                    secondStartDateInput.value = '';
                    secondCourseDurationSelect.disabled = true;
                    secondCourseDurationSelect.value = '';
                }
                // Filter second accommodations based on new course
                filterSecondAccommodations();
            });
            
            secondStartDateInput.addEventListener('change', function() {
                validateSecondCourseStartDate();
                // Always update second course duration options when start date changes
                if (secondCourseSelect.value) {
                    updateSecondCourseMaxWeeks();
                }
                
                // Update second accommodation add-ons based on year-specific settings
                updateSecondAccommodationOptions();
                
                // Trigger auto-calculation if we have all required fields for second course
                const regionSelect = document.getElementById('region_id');
                if (regionSelect.value && secondCourseSelect.value && secondCourseDurationSelect.value && this.value) {
                    autoCalculate();
                }
            });
            
            // Event listener for second course duration select
            secondCourseDurationSelect.addEventListener('change', function() {
                // Update first accommodation duration when second course duration changes
                if (accommodationSelect.value) {
                    populateAccommodationWeeks();
                }
                
                // Trigger auto-calculation if we have all required fields for second course
                const regionSelect = document.getElementById('region_id');
                if (regionSelect.value && secondCourseSelect.value && this.value && secondStartDateInput.value) {
                    autoCalculate();
                }
            });
            
            // Initialize Add Course button state
            updateAddCourseButtonState();
            
            // --- Second Accommodation Functions ---
            
            // Function to enable/disable Add Accommodation button based on first accommodation selection
            function updateAddAccommodationButtonState() {
                const buttonMessage = addAccommodationBtn.parentElement.querySelector('p');
                if (accommodationSelect.value && !isSecondAccommodationVisible) {
                    addAccommodationBtn.disabled = false;
                    if (buttonMessage) {
                        buttonMessage.textContent = 'Click to add a second accommodation';
                    }
                } else {
                    addAccommodationBtn.disabled = true;
                    if (buttonMessage) {
                        if (!accommodationSelect.value) {
                            buttonMessage.textContent = 'Select an accommodation first to enable this option';
                        } else if (isSecondAccommodationVisible) {
                            buttonMessage.textContent = 'Second accommodation is already added';
                        }
                    }
                }
            }
            
            // Function to show second accommodation section
            function showSecondAccommodation() {
                secondAccommodationSection.style.display = 'block';
                isSecondAccommodationVisible = true;
                
                // Enable the second accommodation dropdowns
                secondAccommodationSelect.disabled = false;
                secondAccommodationDurationSelect.disabled = false;
                
                updateAddAccommodationButtonState();
                
                // Filter second accommodation options based on selected school
                if (schoolSelect.value) {
                    filterOptions(secondAccommodationSelect, 'data-school', schoolSelect.value);
                }
                
                // Scroll to second accommodation section
                secondAccommodationSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            // Function to hide second accommodation section
            function hideSecondAccommodation() {
                if (secondAccommodationSection) secondAccommodationSection.style.display = 'none';
                isSecondAccommodationVisible = false;
                
                // Disable the second accommodation dropdowns
                if (secondAccommodationSelect) secondAccommodationSelect.disabled = true;
                if (secondAccommodationDurationSelect) secondAccommodationDurationSelect.disabled = true;
                
                // Reset all second accommodation fields
                if (secondAccommodationSelect) secondAccommodationSelect.value = '';
                if (secondAccommodationDurationSelect) secondAccommodationDurationSelect.value = '';
                if (secondPrivateBathroomCheckbox) secondPrivateBathroomCheckbox.checked = false;
                if (secondDietarySupplementCheckbox) secondDietarySupplementCheckbox.checked = false;
                if (secondChristmasAccommodationSelect) secondChristmasAccommodationSelect.value = 'false';
                
                // Hide optional sections
                if (secondPrivateBathroomDiv) secondPrivateBathroomDiv.style.display = 'none';
                if (secondDietarySupplementDiv) secondDietarySupplementDiv.style.display = 'none';
                if (secondChristmasAccommodationDiv) secondChristmasAccommodationDiv.style.display = 'none';
                
                updateAddAccommodationButtonState();
                
                // Trigger recalculation to remove second accommodation from totals
                autoCalculate();
            }
            
            // Function to update second accommodation options based on selected accommodation
            function updateSecondAccommodationOptions() {
                const secondPrivateBathroomFeeSpan = document.getElementById('second_private_bathroom_fee_display');
                const secondDietarySupplementFeeSpan = document.getElementById('second_dietary_supplement_fee_display');
                
                if (secondAccommodationSelect.value) {
                    const selectedOption = secondAccommodationSelect.options[secondAccommodationSelect.selectedIndex];
                    
                    // Determine year suffix based on second start date
                    let yearSuffix = '2025'; // Default
                    if (secondStartDateInput.value) {
                        const startYear = new Date(secondStartDateInput.value).getFullYear();
                        yearSuffix = startYear === 2026 ? '2026' : '2025';
                    }
                    console.log('Second accommodation year suffix:', yearSuffix);
                    
                    const privateBathroomEnabled = selectedOption.getAttribute(`data-private-bathroom-enabled-${yearSuffix}`) === '1';
                    const privateBathroomFee = selectedOption.getAttribute(`data-private-bathroom-fee-${yearSuffix}`);
                    const dietarySupplementEnabled = selectedOption.getAttribute(`data-dietary-supplement-enabled-${yearSuffix}`) === '1';
                    const dietarySupplementFee = selectedOption.getAttribute(`data-dietary-supplement-fee-${yearSuffix}`);
                    
                    console.log('Second accommodation add-ons:', {
                        privateBathroomEnabled,
                        privateBathroomFee,
                        dietarySupplementEnabled,
                        dietarySupplementFee
                    });
                    
                    // Show/hide private bathroom option
                    if (privateBathroomEnabled) {
                        if (secondPrivateBathroomDiv) secondPrivateBathroomDiv.style.display = 'block';
                        if (secondPrivateBathroomFeeSpan) secondPrivateBathroomFeeSpan.textContent = `£${privateBathroomFee || 0}/week`;
                    } else {
                        if (secondPrivateBathroomDiv) secondPrivateBathroomDiv.style.display = 'none';
                        if (secondPrivateBathroomCheckbox) secondPrivateBathroomCheckbox.checked = false;
                    }
                    
                    // Show/hide dietary supplement option
                    if (dietarySupplementEnabled) {
                        if (secondDietarySupplementDiv) secondDietarySupplementDiv.style.display = 'block';
                        if (secondDietarySupplementFeeSpan) secondDietarySupplementFeeSpan.textContent = `£${dietarySupplementFee || 0}/week`;
                    } else {
                        if (secondDietarySupplementDiv) secondDietarySupplementDiv.style.display = 'none';
                        if (secondDietarySupplementCheckbox) secondDietarySupplementCheckbox.checked = false;
                    }
                    
                    // Populate duration options
                    populateSecondAccommodationDuration();
                    
                    // Update Christmas section visibility
                    updateSecondChristmasSectionVisibility();
                } else {
                    // Hide all optional sections when no accommodation is selected
                    if (secondPrivateBathroomDiv) secondPrivateBathroomDiv.style.display = 'none';
                    if (secondDietarySupplementDiv) secondDietarySupplementDiv.style.display = 'none';
                    if (secondChristmasAccommodationDiv) secondChristmasAccommodationDiv.style.display = 'none';
                    if (secondAccommodationDurationSelect) secondAccommodationDurationSelect.innerHTML = '<option value="">-- Select Duration --</option>';
                }
            }
            
            // Function to populate second accommodation duration options with dynamic limiting
            function populateSecondAccommodationDuration() {
                if (schoolSelect.value) {
                    const school = @json($schools).find(s => s.id == schoolSelect.value);
                    if (school) {
                        const maxWeeks = school.max_weeks || 52;
                        
                        // Calculate total course duration
                        const firstCourseDuration = parseInt(courseDurationSelect.value) || 0;
                        const secondCourseDuration = parseInt(secondCourseDurationSelect.value) || 0;
                        const totalCourseDuration = firstCourseDuration + secondCourseDuration;
                        
                        // Calculate first accommodation duration
                        const firstAccommodationDuration = parseInt(accommodationDurationSelect.value) || 0;
                        
                        // Calculate maximum available weeks for second accommodation
                        const maxAvailableWeeks = Math.max(0, totalCourseDuration - firstAccommodationDuration);
                        
                        // Use the minimum of school max weeks and available weeks
                        const actualMaxWeeks = Math.min(maxWeeks, maxAvailableWeeks);
                        
                        console.log('Second accommodation duration calculation:', {
                            firstCourseDuration,
                            secondCourseDuration,
                            totalCourseDuration,
                            firstAccommodationDuration,
                            maxAvailableWeeks,
                            actualMaxWeeks
                        });
                        
                        // Clear existing options
                        secondAccommodationDurationSelect.innerHTML = '<option value="">-- Select Duration --</option>';
                        
                        // Check if any weeks are available
                        if (actualMaxWeeks <= 0) {
                            // No weeks available - disable dropdown
                            secondAccommodationDurationSelect.disabled = true;
                            const option = document.createElement('option');
                            option.value = '';
                            option.textContent = 'No weeks available';
                            option.disabled = true;
                            secondAccommodationDurationSelect.appendChild(option);
                            console.log('No weeks available for second accommodation');
                        } else {
                            // Enable dropdown and add available week options
                            secondAccommodationDurationSelect.disabled = false;
                            
                            for (let i = 1; i <= actualMaxWeeks; i++) {
                                const option = document.createElement('option');
                                option.value = i;
                                option.textContent = `${i} week${i > 1 ? 's' : ''}`;
                                secondAccommodationDurationSelect.appendChild(option);
                            }
                            console.log(`Added ${actualMaxWeeks} week options for second accommodation`);
                        }
                        
                        // Clear current selection if it exceeds the new maximum
                        const currentValue = parseInt(secondAccommodationDurationSelect.value);
                        if (currentValue > actualMaxWeeks) {
                            secondAccommodationDurationSelect.value = '';
                            console.log('Cleared second accommodation duration as it exceeded available weeks');
                        }
                    }
                }
            }
            
            // Event listeners for Add Accommodation button
            addAccommodationBtn.addEventListener('click', function() {
                if (!addAccommodationBtn.disabled) {
                    showSecondAccommodation();
                }
            });
            
            // Event listeners for Remove Accommodation button
            removeAccommodationBtn.addEventListener('click', function() {
                hideSecondAccommodation();
            });
            
            // Event listener for first accommodation to enable/disable Add Accommodation button
            accommodationSelect.addEventListener('change', function() {
                updateAddAccommodationButtonState();
            });
            
            // Event listener for school change to filter second accommodation options
            schoolSelect.addEventListener('change', function() {
                if (isSecondAccommodationVisible && this.value) {
                    filterOptions(secondAccommodationSelect, 'data-school', this.value);
                }
            });
            
            // Event listener for second accommodation selection
            secondAccommodationSelect.addEventListener('change', function() {
                updateSecondAccommodationOptions();
                updateSecondChristmasSectionVisibility();
                
                // Trigger auto-calculation if we have required fields
                if (this.value && secondAccommodationDurationSelect.value) {
                    autoCalculate();
                }
            });
            
            // Event listener for second accommodation duration
            secondAccommodationDurationSelect.addEventListener('change', function() {
                updateSecondChristmasSectionVisibility();
                
                // Trigger auto-calculation if we have required fields
                if (secondAccommodationSelect.value && this.value) {
                    autoCalculate();
                }
            });
            
            // Event listeners to update second accommodation duration when dependencies change
            courseDurationSelect.addEventListener('change', function() {
                if (isSecondAccommodationVisible && secondAccommodationSelect.value) {
                    populateSecondAccommodationDuration();
                }
            });
            
            secondCourseDurationSelect.addEventListener('change', function() {
                if (isSecondAccommodationVisible && secondAccommodationSelect.value) {
                    populateSecondAccommodationDuration();
                }
            });
            
            accommodationDurationSelect.addEventListener('change', function() {
                if (isSecondAccommodationVisible && secondAccommodationSelect.value) {
                    populateSecondAccommodationDuration();
                }
            });
            
            // Event listeners for second accommodation checkboxes
            secondPrivateBathroomCheckbox.addEventListener('change', function() {
                if (secondAccommodationSelect.value) {
                    autoCalculate();
                }
            });
            
            secondDietarySupplementCheckbox.addEventListener('change', function() {
                if (secondAccommodationSelect.value) {
                    autoCalculate();
                }
            });
            
            secondChristmasAccommodationSelect.addEventListener('change', function() {
                // Update dropdown visibility when Christmas accommodation selection changes
                populateSecondChristmasExtraWeeks(extraAccommodationWeeks);
                
                if (secondAccommodationSelect.value) {
                    autoCalculate();
                }
            });
            
            // Initialize Add Accommodation button state
            updateAddAccommodationButtonState();
            
            // --- Final Initialization ---
            initializeFiltering(); // Initialize dropdown states and filtering based on any pre-filled values
            // Trigger school change if a school is already selected to fetch initial details
            if (schoolSelect.value) {
                 schoolSelect.dispatchEvent(new Event('change'));
                 // The updateChristmasSectionVisibility() will be called after school details are fetched
                 // in the school change event handler's fetch callback
            }
            // We don't need to call updateChristmasSectionVisibility() here directly
            // as it will be called after school details are fetched


        });
    </script>
    @endpush

    {{-- CSS for Christmas accommodation options --}}
    <style>
        /* Style for Christmas accommodation options */
        /* No forced visibility - show/hide based on date overlap */
    </style>

</x-app-layout>
