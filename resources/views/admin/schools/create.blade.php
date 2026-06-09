<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New School') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8"> {{-- Adjusted max-width for more fields --}}
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

                    <form method="POST" action="{{ route('admin.schools.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('School Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- City Selection --}}
                            <div>
                                <x-input-label for="city_id" :value="__('City')" />
                                <select name="city_id" id="city_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select City --</option>
                                    @foreach($cities as $id => $name)
                                        <option value="{{ $id }}" {{ old('city_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('city_id')" class="mt-2" />
                            </div>

                            {{-- Currency Selection --}}
                            <div>
                                <x-input-label for="currency_id" :value="__('Currency')" />
                                <select name="currency_id" id="currency_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">-- Select Currency --</option>
                                    @foreach($currencies as $id => $name)
                                        <option value="{{ $id }}" {{ old('currency_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('currency_id')" class="mt-2" />
                            </div>

                            {{-- Fees Section --}}
                            <div class="md:col-span-2 mt-4 border-t pt-4 border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Fees</h3>
                            </div>

                            {{-- Registration Fee --}}
                            <div>
                                <x-input-label for="registration_fee" :value="__('Registration Fee')" />
                                <x-text-input id="registration_fee" class="block mt-1 w-full" type="number" step="0.01" name="registration_fee" :value="old('registration_fee', 0)" />
                                <x-input-error :messages="$errors->get('registration_fee')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="registration_fee_2026" :value="__('Registration Fee (2026)')" />
                                <x-text-input id="registration_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="registration_fee_2026" :value="old('registration_fee_2026', 0)" />
                                <x-input-error :messages="$errors->get('registration_fee_2026')" class="mt-2" />
                            </div>

                            {{-- Accommodation Placement Fee --}}
                            <div>
                                <x-input-label for="accommodation_fee" :value="__('Accommodation Placement Fee')" />
                                <x-text-input id="accommodation_fee" class="block mt-1 w-full" type="number" step="0.01" name="accommodation_fee" :value="old('accommodation_fee', 0)" />
                                <x-input-error :messages="$errors->get('accommodation_fee')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="accommodation_fee_2026" :value="__('Accommodation Placement Fee (2026)')" />
                                <x-text-input id="accommodation_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="accommodation_fee_2026" :value="old('accommodation_fee_2026', 0)" />
                                <x-input-error :messages="$errors->get('accommodation_fee_2026')" class="mt-2" />
                            </div>

                             {{-- Bank Charges --}}
                            <div>
                                <x-input-label for="bank_charges" :value="__('Bank Charges')" />
                                <x-text-input id="bank_charges" class="block mt-1 w-full" type="number" step="0.01" name="bank_charges" :value="old('bank_charges', 0)" />
                                <x-input-error :messages="$errors->get('bank_charges')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="bank_charges_2026" :value="__('Bank Charges (2026)')" />
                                <x-text-input id="bank_charges_2026" class="block mt-1 w-full" type="number" step="0.01" name="bank_charges_2026" :value="old('bank_charges_2026', 0)" />
                                <x-input-error :messages="$errors->get('bank_charges_2026')" class="mt-2" />
                            </div>

                             {{-- Courier Fee --}}
                             <div>
                                 <x-input-label for="courier_fee" :value="__('Courier Fee')" />
                                 <x-text-input id="courier_fee" class="block mt-1 w-full" type="number" step="0.01" name="courier_fee" :value="old('courier_fee', 0)" />
                                 <x-input-error :messages="$errors->get('courier_fee')" class="mt-2" />
                             </div>
                             <div>
                                 <x-input-label for="courier_fee_2026" :value="__('Courier Fee (2026)')" />
                                 <x-text-input id="courier_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="courier_fee_2026" :value="old('courier_fee_2026', 0)" />
                                 <x-input-error :messages="$errors->get('courier_fee_2026')" class="mt-2" />
                             </div>

                             {{-- Courier Fee Enabled --}}
                             <div>
                                 {{-- Hidden input to ensure a value is always sent --}}
                                 <input type="hidden" name="courier_fee_enabled" value="0">
                                 <label for="courier_fee_enabled" class="inline-flex items-center">
                                     <input id="courier_fee_enabled" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="courier_fee_enabled" value="1" {{ old('courier_fee_enabled', false) ? 'checked' : '' }}>
                                     <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Enable Courier Fee Option') }}</span>
                                 </label>
                                 <p class="text-xs text-gray-500 mt-1">When enabled, the courier fee option will appear in the fee calculator for this school</p>
                                 <x-input-error :messages="$errors->get('courier_fee_enabled')" class="mt-2" />
                             </div>

                             {{-- Insurance Fee Per Week --}}
                             <div>
                                 <x-input-label for="insurance_fee_per_week" :value="__('Insurance Fee (Per Week)')" />
                                 <x-text-input id="insurance_fee_per_week" class="block mt-1 w-full" type="number" step="0.01" name="insurance_fee_per_week" :value="old('insurance_fee_per_week', 0)" />
                                 <x-input-error :messages="$errors->get('insurance_fee_per_week')" class="mt-2" />
                             </div>
                             <div>
                                 <x-input-label for="insurance_fee_per_week_2026" :value="__('Insurance Fee (Per Week) (2026)')" />
                                 <x-text-input id="insurance_fee_per_week_2026" class="block mt-1 w-full" type="number" step="0.01" name="insurance_fee_per_week_2026" :value="old('insurance_fee_per_week_2026', 0)" />
                                 <x-input-error :messages="$errors->get('insurance_fee_per_week_2026')" class="mt-2" />
                             </div>

                            {{-- Books Fee --}}
                            <div>
                                <x-input-label for="books_fee" :value="__('Books Fee (Base)')" />
                                <x-text-input id="books_fee" class="block mt-1 w-full" type="number" step="0.01" name="books_fee" :value="old('books_fee', 0)" />
                                <x-input-error :messages="$errors->get('books_fee')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="books_fee_2026" :value="__('Books Fee (Base) (2026)')" />
                                <x-text-input id="books_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="books_fee_2026" :value="old('books_fee_2026', 0)" />
                                <x-input-error :messages="$errors->get('books_fee_2026')" class="mt-2" />
                            </div>

                            {{-- Books Fee Weeks --}}
                            <div>
                                <x-input-label for="books_weeks" :value="__('Apply Books Fee Every (Weeks)')" />
                                <x-text-input id="books_weeks" class="block mt-1 w-full" type="number" step="1" min="1" name="books_weeks" :value="old('books_weeks')" placeholder="e.g., 4 (Leave blank if one-time)" />
                                <x-input-error :messages="$errors->get('books_weeks')" class="mt-2" />
                            </div>

                            {{-- Books Fee Weeks (2026) --}}
                            <div>
                                <x-input-label for="books_weeks_2026" :value="__('Apply Books Fee Every (Weeks) (2026)')" />
                                <x-text-input id="books_weeks_2026" class="block mt-1 w-full" type="number" step="1" min="1" name="books_weeks_2026" :value="old('books_weeks_2026')" placeholder="e.g., 4 (Leave blank if one-time)" />
                                <x-input-error :messages="$errors->get('books_weeks_2026')" class="mt-2" />
                            </div>

                            {{-- Under 18 Fees Section --}}
                            <div class="md:col-span-2 mt-4 border-t pt-4 border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Under 18 Fees</h3>
                            </div>

                             {{-- Guardianship Fee Per Week --}}
                             <div>
                                 <x-input-label for="guardianship_fee_per_week" :value="__('Guardianship Fee (Per Week)')" />
                                 <x-text-input id="guardianship_fee_per_week" class="block mt-1 w-full" type="number" step="0.01" name="guardianship_fee_per_week" :value="old('guardianship_fee_per_week', 0)" />
                                 <x-input-error :messages="$errors->get('guardianship_fee_per_week')" class="mt-2" />
                             </div>
                             <div>
                                 <x-input-label for="guardianship_fee_per_week_2026" :value="__('Guardianship Fee (Per Week) (2026)')" />
                                 <x-text-input id="guardianship_fee_per_week_2026" class="block mt-1 w-full" type="number" step="0.01" name="guardianship_fee_per_week_2026" :value="old('guardianship_fee_per_week_2026', 0)" />
                                 <x-input-error :messages="$errors->get('guardianship_fee_per_week_2026')" class="mt-2" />
                             </div>

                             {{-- Custodianship Fee --}}
                             <div>
                                 <x-input-label for="custodianship_fee" :value="__('Custodianship Fee (One Time)')" />
                                 <x-text-input id="custodianship_fee" class="block mt-1 w-full" type="number" step="0.01" name="custodianship_fee" :value="old('custodianship_fee', 0)" />
                                 <x-input-error :messages="$errors->get('custodianship_fee')" class="mt-2" />
                             </div>
                             <div>
                                 <x-input-label for="custodianship_fee_2026" :value="__('Custodianship Fee (One Time) (2026)')" />
                                 <x-text-input id="custodianship_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="custodianship_fee_2026" :value="old('custodianship_fee_2026', 0)" />
                                 <x-input-error :messages="$errors->get('custodianship_fee_2026')" class="mt-2" />
                             </div>

                             {{-- Seasonal Fees Section --}}
                             <div class="md:col-span-2 mt-4 border-t pt-4 border-gray-200 dark:border-gray-700">
                                 <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Seasonal Fees</h3>
                             </div>

                             {{-- Summer Fee Per Week --}}
                             <div>
                                 <x-input-label for="summer_fee_per_week" :value="__('Summer Supplement (Per Week)')" />
                                 <x-text-input id="summer_fee_per_week" class="block mt-1 w-full" type="number" step="0.01" name="summer_fee_per_week" :value="old('summer_fee_per_week', 0)" />
                                 <x-input-error :messages="$errors->get('summer_fee_per_week')" class="mt-2" />
                             </div>
                             {{-- Summer Fee Weeks Off --}}
                             <div>
                                 <x-input-label for="summer_fee_weeks_off" :value="__('Waive Summer Supp. After (Weeks)')" />
                                 <x-text-input id="summer_fee_weeks_off" class="block mt-1 w-full" type="number" step="1" min="0" name="summer_fee_weeks_off" :value="old('summer_fee_weeks_off')" placeholder="e.g., 8 (0 or blank = never)" />
                                 <x-input-error :messages="$errors->get('summer_fee_weeks_off')" class="mt-2" />
                             </div>
                             {{-- Summer Start Date --}}
                             <div>
                                 <x-input-label for="summer_start_date" :value="__('Summer Supplement Start Date')" />
                                 <x-text-input id="summer_start_date" class="block mt-1 w-full" type="date" name="summer_start_date" :value="old('summer_start_date')" />
                                 <x-input-error :messages="$errors->get('summer_start_date')" class="mt-2" />
                             </div>
                             {{-- Summer End Date --}}
                             <div>
                                 <x-input-label for="summer_end_date" :value="__('Summer Supplement End Date')" />
                                 <x-text-input id="summer_end_date" class="block mt-1 w-full" type="date" name="summer_end_date" :value="old('summer_end_date')" />
                                 <x-input-error :messages="$errors->get('summer_end_date')" class="mt-2" />
                             </div>
                             {{-- Summer Fee Note --}}
                             <div class="md:col-span-2">
                                 <x-input-label for="summer_fee_note" :value="__('Summer Supplement Note')" />
                                 <x-text-input id="summer_fee_note" class="block mt-1 w-full" type="text" name="summer_fee_note" :value="old('summer_fee_note')" />
                                 <x-input-error :messages="$errors->get('summer_fee_note')" class="mt-2" />
                             </div>


                             {{-- Christmas Fee Per Week --}}
                             <div>
                                 <x-input-label for="christmas_fee_per_week" :value="__('Christmas Supplement (Per Week)')" />
                                 <x-text-input id="christmas_fee_per_week" class="block mt-1 w-full" type="number" step="0.01" name="christmas_fee_per_week" :value="old('christmas_fee_per_week', 0)" />
                                 <x-input-error :messages="$errors->get('christmas_fee_per_week')" class="mt-2" />
                             </div>
                             <div>
                                 <x-input-label for="christmas_fee_per_week_2026" :value="__('Christmas Supplement (Per Week) (2026)')" />
                                 <x-text-input id="christmas_fee_per_week_2026" class="block mt-1 w-full" type="number" step="0.01" name="christmas_fee_per_week_2026" :value="old('christmas_fee_per_week_2026', 0)" />
                                 <x-input-error :messages="$errors->get('christmas_fee_per_week_2026')" class="mt-2" />
                             </div>
                             <div class="md:col-span-1"></div> {{-- Spacer --}}
                             {{-- Christmas Start Date --}}
                             <div>
                                 <x-input-label for="christmas_start_date" :value="__('Christmas Supplement Start Date')" />
                                 <x-text-input id="christmas_start_date" class="block mt-1 w-full" type="date" name="christmas_start_date" :value="old('christmas_start_date')" />
                                 <x-input-error :messages="$errors->get('christmas_start_date')" class="mt-2" />
                             </div>
                             {{-- Christmas End Date --}}
                             <div>
                                 <x-input-label for="christmas_end_date" :value="__('Christmas Supplement End Date')" />
                                 <x-text-input id="christmas_end_date" class="block mt-1 w-full" type="date" name="christmas_end_date" :value="old('christmas_end_date')" />
                                 <x-input-error :messages="$errors->get('christmas_end_date')" class="mt-2" />
                             </div>
                             {{-- Christmas Start Date 2026 --}}
                             <div>
                                 <x-input-label for="christmas_start_date_2026" :value="__('Christmas Supplement Start Date (2026)')" />
                                 <x-text-input id="christmas_start_date_2026" class="block mt-1 w-full" type="date" name="christmas_start_date_2026" :value="old('christmas_start_date_2026')" />
                                 <x-input-error :messages="$errors->get('christmas_start_date_2026')" class="mt-2" />
                             </div>
                             {{-- Christmas End Date 2026 --}}
                             <div>
                                 <x-input-label for="christmas_end_date_2026" :value="__('Christmas Supplement End Date (2026)')" />
                                 <x-text-input id="christmas_end_date_2026" class="block mt-1 w-full" type="date" name="christmas_end_date_2026" :value="old('christmas_end_date_2026')" />
                                 <x-input-error :messages="$errors->get('christmas_end_date_2026')" class="mt-2" />
                             </div>

                             {{-- Extra Accommodation Weeks --}}
                             <div>
                                 <x-input-label for="extra_accommodation_weeks" :value="__('Extra Accommodation Weeks During Christmas (2025)')" />
                                 <x-text-input id="extra_accommodation_weeks" class="block mt-1 w-full" type="number" min="0" max="4" step="1" name="extra_accommodation_weeks" :value="old('extra_accommodation_weeks', 0)" placeholder="e.g., 2 (0 = no extra weeks)" />
                                 <p class="text-xs text-gray-500 mt-1">Number of weeks accommodation can exceed course duration during Christmas period</p>
                                 <x-input-error :messages="$errors->get('extra_accommodation_weeks')" class="mt-2" />
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
                             <a href="{{ route('admin.schools.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Save School') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
