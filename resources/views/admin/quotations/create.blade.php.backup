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
                    <form method="POST" action="{{ route('admin.quotations.calculate') }}" id="calculator-form">
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
                                    <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                                </div>
                                {{-- Course --}}
                                <div>
                                    <x-input-label for="course_id" :value="__('Course')" class="text-gray-700 font-medium"/>
                                    <select name="course_id" id="course_id" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed" required disabled> {{-- Use light bg/dark text --}}
                                        <option value="">-- Select Course --</option>
                                         @foreach($courses as $course)
                                            <option value="{{ $course->id }}" data-school="{{ $course->school_id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->name }}</option>
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
                                             <option value="{{ $accom->id }}" data-school="{{ $accom->school_id }}" data-requires-christmas-supplement="{{ $accom->requires_christmas_supplement ? '1' : '0' }}" {{ old('accommodation_id') == $accom->id ? 'selected' : '' }}>{{ $accom->name }}</option>
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

                                 {{-- Courier Fee (Placeholder - Needs linking to Addon) --}}
                                 <div class="md:col-span-2">
                                     <x-input-label for="courier_fee_option" :value="__('Courier fee (e.g., for I-20/Visa)')" class="text-gray-700 font-medium"/>
                                     <select name="courier_fee_option" id="courier_fee_option" class="block mt-1 w-full border-gray-300 bg-white text-gray-700 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm"> {{-- Use light bg/dark text --}}
                                         <option value="no" {{ old('courier_fee_option', 'no') == 'no' ? 'selected' : '' }}>No</option>
                                         <option value="yes" {{ old('courier_fee_option') == 'yes' ? 'selected' : '' }}>Yes</option>
                                     </select>
                                 </div>
                             </div>
                         </div>

                         {{-- Optional Extras Section --}}
                         <div class="mb-6">
                              <h4 class="text-md font-semibold text-white bg-bayswater-blue p-2 rounded-t-md">Optional extras</h4>
                              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 border border-gray-200 rounded-b-md"> {{-- Add padding/border/bg here --}}
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
                            <h3 class="font-semibold text-lg">Your quote</h3>
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
                                    <p><strong>Duration:</strong> {{ $costBreakdown['course_duration_weeks'] }} weeks</p>
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
                                        <span class="text-sm">{{ $item['name'] }}</span>
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
                                    <span class="text-sm">{{ $discount['name'] }}</span>
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
                            <h3 class="font-semibold text-lg">Your quote</h3>
                        </div>
                        <div class="p-6 text-center text-gray-500">
                            <p>Fill out the form and click Calculate to see your quote.</p>
                        </div>
                    </div>
                    @endisset
                </div>
            </div>

            {{-- Hidden forms for PDF and Print actions --}}
            <form id="pdf-form" action="{{ route('admin.quotations.pdf') }}" method="POST" target="_blank" style="display: none;">
                @csrf
                {{-- Form fields will be populated by JavaScript --}}
            </form>

            <form id="print-form" action="{{ route('admin.quotations.print') }}" method="POST" target="_blank" style="display: none;">
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

        console.log('Script loaded');

        // Debug flag for Christmas settings - set to true to enable debugging
        const debugChristmasSettings = false; // Set to false for production

        // Global variables to store school-specific Christmas dates
        let schoolChristmasStartDate = null;
        let schoolChristmasEndDate = null;
        let extraAccommodationWeeks = 0; // Initialize, will be fetched via AJAX

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

            // Function to populate Christmas extra weeks dropdown
            function populateChristmasExtraWeeks(maxWeeks) {
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
                christmasExtraWeeksDiv.style.display = (christmasAccommodationSelect.value === 'yes') ? 'block' : 'none';

                if (debugChristmasSettings) {
                    console.log('Christmas extra weeks dropdown populated with', christmasExtraWeeksSelect.options.length, 'options');
                    console.log('Extra weeks div display:', christmasExtraWeeksDiv.style.display);
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
                    const accomEndDate = new Date(startDate);
                    accomEndDate.setDate(accomEndDate.getDate() + (accomWeeks * 7));

                    const christmasStart = new Date(schoolChristmasStartDate + 'T00:00:00');
                    const christmasEnd = new Date(schoolChristmasEndDate + 'T00:00:00');
                    // Adjust end date to be inclusive for comparison (end of the day)
                    christmasEnd.setDate(christmasEnd.getDate() + 1);

                    // Check for overlap: (StartA <= EndB) and (EndA >= StartB)
                    const overlaps = (startDate < christmasEnd && accomEndDate >= christmasStart);

                    if (debugChristmasSettings) {
                        console.log('Christmas overlap check:', {
                            accomStart: startDate.toISOString().split('T')[0],
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

            // Function to update the visibility and content of the Christmas section
            function updateChristmasSectionVisibility() {
                if (!accommodationSelect.value) {
                    // Hide if no accommodation is selected
                    christmasAccommodationDiv.style.display = 'none';
                    if (debugChristmasSettings) console.log('Hiding Christmas section: No accommodation selected.');
                    return;
                }

                const overlaps = checkChristmasOverlap();

                if (overlaps) {
                    christmasAccommodationDiv.style.display = 'block';
                    christmasPeriodInfo.textContent = `Christmas period: ${formatDate(schoolChristmasStartDate)} to ${formatDate(schoolChristmasEndDate)}`;
                    populateChristmasExtraWeeks(extraAccommodationWeeks); // Repopulate/show/hide extra weeks
                    if (debugChristmasSettings) console.log('Showing Christmas section. Overlap detected.');
                } else {
                    christmasAccommodationDiv.style.display = 'none';
                    // Reset selections when hidden? Optional, maybe keep selection if user toggles back quickly.
                    // christmasAccommodationSelect.value = 'no';
                    // christmasExtraWeeksDiv.style.display = 'none';
                    if (debugChristmasSettings) console.log('Hiding Christmas section: No overlap detected.');
                }
            }


            // --- Filtering Functions --- (Keep existing filterOptions function)
            function filterOptions(targetSelect, attributeName, filterValue) {
                console.log(`Filtering ${targetSelect.id} by ${attributeName}=${filterValue}`);

                // First, hide all options except the default one
                for (let i = 0; i < targetSelect.options.length; i++) {
                    const option = targetSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                // If no filter value, show all options and return
                if (!filterValue) {
                    console.log(`  No filter value provided, showing all options in ${targetSelect.id}`);
                    for (let i = 0; i < targetSelect.options.length; i++) {
                        const option = targetSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = '';
                    }
                    return;
                }

                const initialValue = targetSelect.value;
                let firstVisibleValue = null;
                let foundSelected = false;
                let visibleCount = 0;

                // Now show only options that match the filter
                for (let i = 0; i < targetSelect.options.length; i++) {
                    const option = targetSelect.options[i];
                    if (option.value === "") continue; // Skip the default option

                    const attrValue = option.getAttribute(attributeName);
                    console.log(`  Checking option ${option.textContent} (${option.value}): ${attributeName}=${attrValue} vs ${filterValue}`);

                    // Convert both to strings for comparison
                    const shouldShow = String(attrValue) === String(filterValue);

                    // Set visibility
                    option.style.display = shouldShow ? '' : 'none';

                    if (shouldShow) {
                        visibleCount++;
                        console.log(`  - SHOWING: ${option.textContent} (${option.value})`);

                        if (!firstVisibleValue) {
                            firstVisibleValue = option.value;
                        }

                        if (option.value === initialValue) {
                            foundSelected = true;
                        }
                    } else {
                        console.log(`  - HIDING: ${option.textContent} (${option.value})`);
                    }
                }

                // Only auto-select if the dropdown is enabled and there's only one option
                // Do not auto-select if the dropdown is disabled
                if (!targetSelect.disabled) {
                    // If the previously selected option is now hidden, select the first visible option
                    if (initialValue && !foundSelected) {
                        targetSelect.value = firstVisibleValue || "";
                        targetSelect.dispatchEvent(new Event('change'));
                        console.log(`  Changed selection to ${targetSelect.value}`);
                    } else if (!initialValue && firstVisibleValue && visibleCount === 1 && false) { // Disabled auto-selection
                        // Auto-select if there's only one option available - DISABLED
                        // targetSelect.value = firstVisibleValue;
                        // targetSelect.dispatchEvent(new Event('change'));
                        console.log(`  NOT auto-selecting the only available option: ${firstVisibleValue}`);
                    }
                } else {
                    console.log(`  Dropdown is disabled, not auto-selecting any option`);
                }

                console.log(`  Found ${visibleCount} visible options in ${targetSelect.id}`);
                if (visibleCount === 0) {
                    console.warn(`  WARNING: No visible options found in ${targetSelect.id} for ${attributeName}=${filterValue}`);
                    // Reset to default option
                    targetSelect.value = "";
                }
            }

            function toggleAccommodationDuration() {
                const show = accommodationSelect.value !== "";
                accommodationDurationDiv.style.display = show ? '' : 'none';
                accommodationDurationSelect.required = show;
                accommodationDurationSelect.disabled = !show || accommodationSelect.disabled;

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

                    // Show only cities that match the selected country
                    for (let i = 0; i < citySelect.options.length; i++) {
                        const option = citySelect.options[i];
                        if (option.value === "") continue; // Skip the default option

                        const countryId = option.getAttribute('data-country');
                        option.style.display = (String(countryId) === String(this.value)) ? '' : 'none';
                    }
                } else {
                    // Disable city dropdown if no country selected
                    citySelect.disabled = true;

                    // Hide all options except the default one
                    for (let i = 0; i < citySelect.options.length; i++) {
                        const option = citySelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
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

                // Clear other dropdowns
                for (let i = 0; i < schoolSelect.options.length; i++) {
                    const option = schoolSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                for (let i = 0; i < courseSelect.options.length; i++) {
                    const option = courseSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                for (let i = 0; i < accommodationSelect.options.length; i++) {
                    const option = accommodationSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }
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

                    // Show only schools that match the selected city
                    for (let i = 0; i < schoolSelect.options.length; i++) {
                        const option = schoolSelect.options[i];
                        if (option.value === "") continue; // Skip the default option

                        const cityId = option.getAttribute('data-city');
                        option.style.display = (String(cityId) === String(this.value)) ? '' : 'none';
                    }
                } else {
                    // Disable school dropdown if no city selected
                    schoolSelect.disabled = true;

                    // Hide all options except the default one
                    for (let i = 0; i < schoolSelect.options.length; i++) {
                        const option = schoolSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                // Disable all subsequent dropdowns
                courseSelect.disabled = true;
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // arrivalAirportSelect.disabled = true; // Already disabled above
                // departureAirportSelect.disabled = true;

                // Clear other dropdowns
                for (let i = 0; i < courseSelect.options.length; i++) {
                    const option = courseSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                for (let i = 0; i < accommodationSelect.options.length; i++) {
                    const option = accommodationSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }
            });

            // When school changes, filter courses and accommodations, fetch details & airports
            schoolSelect.addEventListener('change', function() {
                console.log('School changed to: ' + this.value);
                const selectedSchoolId = this.value;

                // Reset dependent dropdowns
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

                // Reset course duration
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';

                // Fetch school details (Christmas dates, extra weeks) & Airports
                if (selectedSchoolId) {
                    // Fetch school details
                    fetch(`/admin/schools/${selectedSchoolId}/details`)
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
                        })
                        .catch(error => {
                            console.error('Error fetching school details:', error);
                            extraAccommodationWeeks = 0;
                            schoolChristmasStartDate = null;
                            schoolChristmasEndDate = null;
                            updateChristmasSectionVisibility();
                        });

                    // Fetch Airports
                    fetch(`/admin/schools/${selectedSchoolId}/airports`) // New endpoint
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

                } else {
                     // No school selected, ensure Christmas section is hidden
                     updateChristmasSectionVisibility();
                     // Clear and disable airport dropdowns
                     populateAirportDropdowns([]);
                     arrivalAirportSelect.disabled = true;
                     departureAirportSelect.disabled = true;
                }

                // Enable/disable course dropdown based on school selection
                if (selectedSchoolId) {
                    // Enable course dropdown
                    courseSelect.disabled = false;
                    // Enable accommodation dropdown
                    accommodationSelect.disabled = false;

                    // Show only courses that match the selected school
                    for (let i = 0; i < courseSelect.options.length; i++) {
                        const option = courseSelect.options[i];
                        if (option.value === "") continue; // Skip the default option

                        const schoolId = option.getAttribute('data-school');
                        option.style.display = (String(schoolId) === String(selectedSchoolId)) ? '' : 'none';
                    }

                    // Show only accommodations that match the selected school
                    for (let i = 0; i < accommodationSelect.options.length; i++) {
                        const option = accommodationSelect.options[i];
                        if (option.value === "") continue; // Skip the default option

                        const schoolId = option.getAttribute('data-school');
                        option.style.display = (String(schoolId) === String(selectedSchoolId)) ? '' : 'none';
                    }
                } else {
                    // Disable course and accommodation dropdowns if no school selected
                    courseSelect.disabled = true;
                    accommodationSelect.disabled = true;

                    // Hide all course options except the default one
                    for (let i = 0; i < courseSelect.options.length; i++) {
                        const option = courseSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }

                    // Hide all accommodation options except the default one
                    for (let i = 0; i < accommodationSelect.options.length; i++) {
                        const option = accommodationSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                // Disable all subsequent dropdowns
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // Airport dropdowns enabled/disabled based on fetch success/failure and start date selection
            });

            // --- Function to Populate Airport Dropdowns ---
            function populateAirportDropdowns(airports) {
                // Clear existing options (except the default)
                arrivalAirportSelect.innerHTML = '<option value="">-- Not Required --</option>';
                departureAirportSelect.innerHTML = '<option value="">-- Not Required --</option>';

                if (airports && airports.length > 0) {
                    airports.forEach(airport => {
                        const option = document.createElement('option');
                        option.value = airport.id;
                        option.textContent = airport.name;
                        arrivalAirportSelect.appendChild(option.cloneNode(true));
                        departureAirportSelect.appendChild(option.cloneNode(true));
                    });
                }
                // Reset selection
                arrivalAirportSelect.value = '';
                departureAirportSelect.value = '';
            }

            // Add event listener for course select
            courseSelect.addEventListener('change', function() {
                console.log('Course changed to: ' + this.value);

                // Enable/disable start date and course duration based on course selection
                if (this.value) {
                    // Enable start date input
                    startDateInput.disabled = false;
                    // Enable course duration dropdown
                    courseDurationSelect.disabled = false;

                    // Update max weeks when course changes
                    updateMaxWeeks();
                } else {
                    // Disable start date and course duration if no course selected
                    startDateInput.disabled = true;
                    courseDurationSelect.disabled = true;
                }
                // Update Christmas section visibility (in case duration changes affect overlap)
                updateChristmasSectionVisibility();
                // No need to call autoCalculate here as it will be triggered by the course duration change
            });

            // Add event listener for course duration select
            courseDurationSelect.addEventListener('change', function() {
                // No need to update accommodation weeks when course duration changes
                // They are now independent
                console.log('Course duration changed to: ' + this.value);
                updateChristmasSectionVisibility(); // Check overlap with new duration

                // Trigger calculation if needed
                if (startDateInput.value && courseSelect.value && this.value) {
                    autoCalculate();
                }
            });

            // Add event listener for accommodation select
            accommodationSelect.addEventListener('change', function() {
                console.log('Accommodation changed to: ' + this.value);
                // Toggle accommodation duration visibility and enable/disable
                toggleAccommodationDuration(); // This now calls updateChristmasSectionVisibility internally
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


            // --- Accommodation Weeks Dropdown Population --- (Keep existing function)
            function populateAccommodationWeeks() {
                console.log('Populating accommodation weeks dropdown');

                // Get the selected course duration
                const courseDuration = parseInt(courseDurationSelect.value) || 0;
                console.log('Selected course duration: ' + courseDuration + ' weeks');

                // Limit accommodation weeks to course duration
                let maxAccommodationWeeks = courseDuration;
                console.log('Limiting accommodation weeks to course duration: ' + maxAccommodationWeeks);

                // If no course duration or it's invalid, set a reasonable default
                if (!maxAccommodationWeeks || maxAccommodationWeeks <= 0) {
                    maxAccommodationWeeks = 52; // Default maximum of 52 weeks (1 year)
                    console.log('No valid course duration. Using default max: ' + maxAccommodationWeeks);
                }

                // Get the old selected value if any
                const oldValue = '{{ old("accommodation_duration_weeks") }}';
                console.log('Old accommodation weeks value: ' + oldValue);
                let valueSelected = false;

                // Populate dropdown options
                accommodationDurationSelect.innerHTML = '<option value="">-- Select Accommodation Duration --</option>';

                // Generate options from 1 to maxAccommodationWeeks
                for (let i = 1; i <= maxAccommodationWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} week${i > 1 ? 's' : ''}`;

                    // Select the old value if it matches
                    if (oldValue && parseInt(oldValue) === i) {
                        option.selected = true;
                        valueSelected = true;
                    }

                    accommodationDurationSelect.appendChild(option);
                }

                console.log('Accommodation weeks dropdown populated with ' + maxAccommodationWeeks + ' options');

                // If no value was selected, auto-select the first option (1 week)
                if (!valueSelected && accommodationDurationSelect.options.length > 1) {
                    accommodationDurationSelect.selectedIndex = 1; // Select the first week option
                    console.log('Auto-selected first accommodation duration option: 1 week');
                    // Don't trigger change event here - we'll handle calculation in toggleAccommodationDuration
                }

                return valueSelected;
            }

            // --- Max Weeks Calculation & Dropdown Population --- (Keep existing function)
            function updateMaxWeeks() {
                const selectedCourseId = courseSelect.value;
                let maxWeeks = 52; // Default max if no limit or no prices
                let minWeeks = 1; // Default min

                // Clear existing options and set default/disabled state
                courseDurationSelect.innerHTML = '<option value="">-- Select Course Duration --</option>';

                // Get the old selected value if any
                const oldValue = '{{ old("course_duration_weeks") }}';
                let valueSelected = false;

                // Populate dropdown options
                for (let i = minWeeks; i <= maxWeeks; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = `${i} week${i > 1 ? 's' : ''}`;

                    // Select the old value if it matches
                    if (oldValue && parseInt(oldValue) === i) {
                        option.selected = true;
                        valueSelected = true;
                    }

                    courseDurationSelect.appendChild(option);
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

            // Function to calculate and display exact age in years, months, and days
            function calculateAge(birthDate) {
                const today = new Date();
                const birth = new Date(birthDate);

                // Basic validation to prevent invalid calculations
                if (birth > today) {
                    ageDisplay.textContent = 'Future date';
                    return 0;
                }

                // Calculate years
                let years = today.getFullYear() - birth.getFullYear();

                // Calculate months
                let months = today.getMonth() - birth.getMonth();
                if (months < 0) {
                    years--;
                    months += 12;
                }

                // Calculate days
                let days = today.getDate() - birth.getDate();
                if (days < 0) {
                    // Borrow from months
                    months--;
                    if (months < 0) {
                        years--;
                        months += 12;
                    }

                    // Get days in the previous month
                    const lastMonth = new Date(today.getFullYear(), today.getMonth(), 0);
                    days += lastMonth.getDate();
                }

                // Format the age string
                let ageString = `Age: ${years}y`;
                if (months > 0 || days > 0) {
                    ageString += ` ${months}m`;
                }
                if (days > 0) {
                    ageString += ` ${days}d`;
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
                if (courseSelect.value && courseDurationSelect.value) {
                    autoCalculate();
                }
            });

            // --- Initialize Flatpickr for Start Date --- (Keep existing logic)
            const currentYear = new Date().getFullYear();
            flatpickr(startDateInput, {
                minDate: `${currentYear}-01-01`, // Disable dates before current year
                maxDate: `${currentYear + 1}-12-31`, // Disable dates after next year
                disable: [
                    function(date) {
                        // Return true to disable date
                        // Disable weekends (0 = Sunday, 6 = Saturday) and non-Mondays (1 = Monday)
                        return (date.getDay() === 0 || date.getDay() > 1);
                    }
                ],
                dateFormat: "Y-m-d", // Ensure format matches HTML5 date input
                onChange: function(selectedDates, dateStr, instance) {
                    // Trigger change event manually for Flatpickr
                    startDateInput.dispatchEvent(new Event('change'));
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
                if (christmasAccommodationDiv.style.display !== 'none') {
                    if (debugChristmasSettings) {
                        console.log('\n--- Christmas Accommodation in Calculation ---');
                        console.log('Christmas accommodation div display:', christmasAccommodationDiv.style.display);
                        console.log('Christmas accommodation selection:', christmasAccommodationSelect.value);
                        console.log('Christmas extra weeks div display:', christmasExtraWeeksDiv.style.display);
                        if (christmasExtraWeeksDiv.style.display !== 'none') {
                            console.log('Christmas extra weeks selection:', christmasExtraWeeksSelect.value);
                        }
                    }

                    // Add the Christmas accommodation selection to the form data
                    formData.append('christmas_accommodation', christmasAccommodationSelect.value);
                    console.log('Adding Christmas accommodation option:', christmasAccommodationSelect.value);

                    // If Christmas accommodation is 'yes' and extra weeks are available AND VISIBLE, add them
                    if (christmasAccommodationSelect.value === 'yes' && extraAccommodationWeeks > 0 && christmasExtraWeeksDiv.style.display !== 'none') {
                        // Get the selected extra weeks value
                        let extraWeeksValue = christmasExtraWeeksSelect.value || '1'; // Default to 1 if somehow not selected
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
                if (arrivalAirportSelect.value) {
                    formData.append('arrival_transfer_airport_id', arrivalAirportSelect.value);
                }
                 if (departureAirportSelect.value) {
                    formData.append('departure_transfer_airport_id', departureAirportSelect.value);
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

                    // Show all country options
                    for (let i = 0; i < countrySelect.options.length; i++) {
                        const option = countrySelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = '';
                    }
                } else {
                    // Disable country dropdown if no region selected
                    countrySelect.disabled = true;

                    // Hide all options except the default one
                    for (let i = 0; i < countrySelect.options.length; i++) {
                        const option = countrySelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                // Disable all subsequent dropdowns
                citySelect.disabled = true;
                schoolSelect.disabled = true;
                courseSelect.disabled = true;
                startDateInput.disabled = true;
                courseDurationSelect.disabled = true;
                accommodationSelect.disabled = true;
                accommodationDurationSelect.disabled = true;
                // arrivalAirportSelect.disabled = true; // Already disabled above
                // departureAirportSelect.disabled = true;

                // Hide all options in dependent dropdowns
                for (let i = 0; i < citySelect.options.length; i++) {
                    const option = citySelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                for (let i = 0; i < schoolSelect.options.length; i++) {
                    const option = schoolSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                for (let i = 0; i < courseSelect.options.length; i++) {
                    const option = courseSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                for (let i = 0; i < accommodationSelect.options.length; i++) {
                    const option = accommodationSelect.options[i];
                    if (option.value === "") continue; // Skip the default option
                    option.style.display = 'none';
                }

                // Only auto-calculate if we have all required fields
                // if (this.value && countrySelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                //     autoCalculate();
                // } // Calculation should not trigger just on region change
            });

            // For course and duration, we'll just update the weeks dropdown without calculating
            courseSelect.addEventListener('change', function() {
                updateMaxWeeks();
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');

                // Only auto-calculate if we have all required fields
                if (regionSelect.value && courseSelect.value && courseDurationSelect.value && startDateInput.value) {
                    autoCalculate();
                }
            });

            // This is a duplicate event listener that was causing issues
            // The main event listener is defined above

            // For start date, we'll check if all required fields are filled before calculating
            startDateInput.addEventListener('change', function() {
                // Get the region dropdown
                const regionSelect = document.getElementById('region_id');
                // const extraAccommodationWeeks = {{ $school->extra_accommodation_weeks ?? 0 }}; // Use fetched value

                // Update Christmas section visibility based on the new date
                updateChristmasSectionVisibility();

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

                // Hide all options in country dropdown except the default one
                if (!countrySelect.value) {
                    for (let i = 0; i < countrySelect.options.length; i++) {
                        const option = countrySelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                // Clear all dropdowns to prevent auto-selection
                if (!citySelect.value) {
                    // Hide all options in city dropdown except the default one
                    for (let i = 0; i < citySelect.options.length; i++) {
                        const option = citySelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                if (!schoolSelect.value) {
                    // Hide all options in school dropdown except the default one
                    for (let i = 0; i < schoolSelect.options.length; i++) {
                        const option = schoolSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                if (!courseSelect.value) {
                    // Hide all options in course dropdown except the default one
                    for (let i = 0; i < courseSelect.options.length; i++) {
                        const option = courseSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
                }

                if (!accommodationSelect.value) {
                    // Hide all options in accommodation dropdown except the default one
                    for (let i = 0; i < accommodationSelect.options.length; i++) {
                        const option = accommodationSelect.options[i];
                        if (option.value === "") continue; // Skip the default option
                        option.style.display = 'none';
                    }
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

                // Helper to format currency
                const formatCurrency = (amount) => {
                    // Ensure amount is treated as a number, default to 0 if invalid
                    const numericAmount = parseFloat(amount);
                    const value = isNaN(numericAmount) ? 0 : numericAmount;
                    return `${costBreakdown.currency_symbol || ''}${value.toFixed(2)}`;
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

                let html = `
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="bg-bayswater-blue text-white p-3">
                            <h3 class="font-semibold text-lg">Your quote</h3>
                        </div>
                        <div class="p-4">`;

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

                // Course Section
                let courseTuition = 0;
                let courseName = costBreakdown.course_name || 'Course details missing';
                let courseDuration = costBreakdown.course_duration_weeks || 'N/A';
                costBreakdown.items.forEach(item => {
                    if (item.category === 'tuition') {
                        courseTuition = item.amount;
                        courseName = item.name; // Use name from item if available
                    }
                });

                html += `<div class="mb-6">
                            <h4 class="font-semibold text-bayswater-blue mb-2">Course</h4>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm">${courseName}</span>
                                <span class="font-semibold">${formatCurrency(courseTuition)}</span>
                            </div>
                            <div class="text-sm text-gray-600 mt-2">
                                <p><strong>Start date:</strong> ${formatDateDisplay(costBreakdown.course_start_date)}</p>
                                <p><strong>Duration:</strong> ${courseDuration} weeks</p>
                            </div>
                         </div>`;

                // Accommodation Section
                let accommodationTotal = costBreakdown.subtotals?.accommodation || 0;
                let accommodationName = '';
                costBreakdown.items.forEach(item => {
                    if (item.category === 'accommodation' && !item.name.includes('Fee')) { // Exclude placement fee
                        accommodationName = item.name;
                    }
                });

                if (accommodationTotal > 0 || accommodationName) { // Show section if there's a cost or a name
                    html += `<div class="mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Accommodation</h4>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm">${accommodationName || 'Accommodation'}</span>
                                    <span class="font-semibold">${formatCurrency(accommodationTotal)}</span>
                                </div>
                             </div>`;
                }

                // Sub Total (Course + Accommodation)
                let subTotalCourseAccom = (costBreakdown.subtotals?.tuition || 0) + (costBreakdown.subtotals?.accommodation || 0);
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
                                        <span class="text-sm">${item.name}</span>
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

                // Discounts Section
                if (costBreakdown.discounts && costBreakdown.discounts.length > 0) {
                    html += `<div class="mt-6 mb-6">
                                <h4 class="font-semibold text-bayswater-blue mb-2">Discounts Applied</h4>`;
                    costBreakdown.discounts.forEach(discount => {
                        // Only display if amount > 0 (waivers are handled implicitly by fee not being added or shown)
                        if (discount.amount > 0) {
                             html += `<div class="flex justify-between items-center mb-1 text-green-600">
                                         <span class="text-sm">${discount.name}</span>
                                         <span class="font-semibold">-${formatCurrency(discount.amount)}</span>
                                      </div>`;
                        }
                    });
                    html += `</div>`;
                }

                // Notes Section
                if (costBreakdown.notes && costBreakdown.notes.length > 0) {
                    html += `<div class="mt-6 mb-6">
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

                // Re-initialize buttons after updating HTML
                initializePrintAndPdfButtons();
            }


            // --- Final Initialization ---
            initializeFiltering(); // Initialize dropdown states and filtering based on any pre-filled values
            // Trigger school change if a school is already selected to fetch initial details
            if (schoolSelect.value) {
                 schoolSelect.dispatchEvent(new Event('change'));
            }


        });
    </script>
    @endpush

    {{-- CSS for Christmas accommodation options --}}
    <style>
        /* Style for Christmas accommodation options */
        /* No forced visibility - show/hide based on date overlap */
    </style>

</x-app-layout>
