<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add New Accommodation') }}
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

                    <form method="POST" action="{{ route('admin.accommodations.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div class="md:col-span-2">
                                <x-input-label for="name" :value="__('Accommodation Name')" />
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

                            {{-- Type --}}
                            <div>
                                <x-input-label for="type" :value="__('Type (e.g., Homestay, Residence)')" />
                                <x-text-input id="type" class="block mt-1 w-full" type="text" name="type" :value="old('type')" />
                                <x-input-error :messages="$errors->get('type')" class="mt-2" />
                            </div>

                            {{-- Room Type --}}
                            <div>
                                <x-input-label for="room_type" :value="__('Room Type (e.g., Single, Twin)')" />
                                <x-text-input id="room_type" class="block mt-1 w-full" type="text" name="room_type" :value="old('room_type')" />
                                <x-input-error :messages="$errors->get('room_type')" class="mt-2" />
                            </div>

                            {{-- Meal Plan --}}
                            <div>
                                <x-input-label for="meal_plan" :value="__('Meal Plan (e.g., Half Board, None)')" />
                                <x-text-input id="meal_plan" class="block mt-1 w-full" type="text" name="meal_plan" :value="old('meal_plan')" />
                                <x-input-error :messages="$errors->get('meal_plan')" class="mt-2" />
                            </div>

                             {{-- Min Age --}}
                             <div>
                                 <x-input-label for="min_age" :value="__('Min Age (Optional)')" />
                                 <x-text-input id="min_age" class="block mt-1 w-full" type="number" step="1" min="0" name="min_age" :value="old('min_age')" />
                                 <x-input-error :messages="$errors->get('min_age')" class="mt-2" />
                             </div>

                             {{-- Max Age --}}
                             <div>
                                 <x-input-label for="max_age" :value="__('Max Age (Optional)')" />
                                 <x-text-input id="max_age" class="block mt-1 w-full" type="number" step="1" min="0" name="max_age" :value="old('max_age')" />
                                 <x-input-error :messages="$errors->get('max_age')" class="mt-2" />
                             </div>

                            {{-- Description --}}
                            <div class="md:col-span-2">
                                <x-input-label for="description" :value="__('Description (Optional)')" />
                                <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description') }}</textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-2" />
                            </div>

                             {{-- Seasonal Fees Section --}}
                             <div class="md:col-span-2 mt-4 border-t pt-4 border-gray-200 dark:border-gray-700">
                                 <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">Seasonal Fees / Requirements</h3>
                             </div>

                             {{-- Summer Supplement Fields - Side by Side Layout --}}
                             <div class="md:col-span-2">
                                 <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                     {{-- 2025 Summer Supplement --}}
                                     <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                         <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3">2025 Summer Supplement</h4>
                                         
                                         <div class="space-y-4">
                                             {{-- Summer Fee Per Week 2025 --}}
                                             <div>
                                                 <x-input-label for="summer_fee_per_week" :value="__('Fee Per Week (2025)')" />
                                                 <x-text-input id="summer_fee_per_week" class="block mt-1 w-full" type="number" step="0.01" name="summer_fee_per_week" :value="old('summer_fee_per_week', 0)" />
                                                 <x-input-error :messages="$errors->get('summer_fee_per_week')" class="mt-2" />
                                             </div>
                                             
                                             {{-- Summer Start Date 2025 --}}
                                             <div>
                                                 <x-input-label for="summer_start_date" :value="__('Start Date (2025)')" />
                                                 <x-text-input id="summer_start_date" class="block mt-1 w-full" type="date" name="summer_start_date" :value="old('summer_start_date')" />
                                                 <x-input-error :messages="$errors->get('summer_start_date')" class="mt-2" />
                                             </div>
                                             
                                             {{-- Summer End Date 2025 --}}
                                             <div>
                                                 <x-input-label for="summer_end_date" :value="__('End Date (2025)')" />
                                                 <x-text-input id="summer_end_date" class="block mt-1 w-full" type="date" name="summer_end_date" :value="old('summer_end_date')" />
                                                 <x-input-error :messages="$errors->get('summer_end_date')" class="mt-2" />
                                             </div>
                                             
                                             {{-- Summer Fee Note 2025 --}}
                                             <div>
                                                 <x-input-label for="summer_fee_note" :value="__('Note (2025)')" />
                                                 <x-text-input id="summer_fee_note" class="block mt-1 w-full" type="text" name="summer_fee_note" :value="old('summer_fee_note')" />
                                                 <x-input-error :messages="$errors->get('summer_fee_note')" class="mt-2" />
                                             </div>
                                         </div>
                                     </div>

                                     {{-- 2026 Summer Supplement --}}
                                     <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                         <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-3">2026 Summer Supplement</h4>
                                         
                                         <div class="space-y-4">
                                             {{-- Summer Fee Per Week 2026 --}}
                                             <div>
                                                 <x-input-label for="summer_fee_per_week_2026" :value="__('Fee Per Week (2026)')" />
                                                 <x-text-input id="summer_fee_per_week_2026" class="block mt-1 w-full" type="number" step="0.01" name="summer_fee_per_week_2026" :value="old('summer_fee_per_week_2026', 0)" />
                                                 <x-input-error :messages="$errors->get('summer_fee_per_week_2026')" class="mt-2" />
                                             </div>
                                             
                                             {{-- Summer Start Date 2026 --}}
                                             <div>
                                                 <x-input-label for="summer_start_date_2026" :value="__('Start Date (2026)')" />
                                                 <x-text-input id="summer_start_date_2026" class="block mt-1 w-full" type="date" name="summer_start_date_2026" :value="old('summer_start_date_2026')" />
                                                 <x-input-error :messages="$errors->get('summer_start_date_2026')" class="mt-2" />
                                             </div>
                                             
                                             {{-- Summer End Date 2026 --}}
                                             <div>
                                                 <x-input-label for="summer_end_date_2026" :value="__('End Date (2026)')" />
                                                 <x-text-input id="summer_end_date_2026" class="block mt-1 w-full" type="date" name="summer_end_date_2026" :value="old('summer_end_date_2026')" />
                                                 <x-input-error :messages="$errors->get('summer_end_date_2026')" class="mt-2" />
                                             </div>
                                             
                                             {{-- Summer Fee Note 2026 --}}
                                             <div>
                                                 <x-input-label for="summer_fee_note_2026" :value="__('Note (2026)')" />
                                                 <x-text-input id="summer_fee_note_2026" class="block mt-1 w-full" type="text" name="summer_fee_note_2026" :value="old('summer_fee_note_2026')" />
                                                 <x-input-error :messages="$errors->get('summer_fee_note_2026')" class="mt-2" />
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>

                             {{-- Requires Guardianship Checkbox --}}
                             <div class="block mt-4 md:col-span-1">
                                 <label for="requires_guardianship" class="inline-flex items-center">
                                     <input id="requires_guardianship" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="requires_guardianship" value="1" {{ old('requires_guardianship') ? 'checked' : '' }}>
                                     <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Requires Guardianship (U18)') }}</span>
                                 </label>
                                  <x-input-error :messages="$errors->get('requires_guardianship')" class="mt-2" />
                             </div>

                             {{-- Requires Christmas Supplement Checkbox --}}
                             <div class="block mt-4 md:col-span-1">
                                 <label for="requires_christmas_supplement" class="inline-flex items-center">
                                     <input id="requires_christmas_supplement" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="requires_christmas_supplement" value="1" {{ old('requires_christmas_supplement') ? 'checked' : '' }}>
                                     <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Don\'t Apply Christmas Supplement') }}</span>
                                 </label>
                                  <x-input-error :messages="$errors->get('requires_christmas_supplement')" class="mt-2" />
                             </div>

                        </div> {{-- End Grid --}}

                        {{-- Optional Features Section --}}
                        <div class="mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Optional Features</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- 2025 Add-on Fees --}}
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-3">2025 Add-on Fees</h4>
                                    
                                    <div class="space-y-4">
                                        {{-- Private Bathroom 2025 --}}
                                        <div>
                                            <label for="private_bathroom_enabled" class="inline-flex items-center mb-2">
                                                <input id="private_bathroom_enabled" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="private_bathroom_enabled" value="1" {{ old('private_bathroom_enabled') ? 'checked' : '' }}>
                                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Private Bathroom Available (2025)') }}</span>
                                            </label>
                                            <x-input-error :messages="$errors->get('private_bathroom_enabled')" class="mt-2" />
                                            
                                            <div>
                                                <x-input-label for="private_bathroom_fee" :value="__('Private Bathroom Fee (2025)')" />
                                                <x-text-input id="private_bathroom_fee" class="block mt-1 w-full" type="number" step="0.01" name="private_bathroom_fee" :value="old('private_bathroom_fee', 0)" />
                                                <x-input-error :messages="$errors->get('private_bathroom_fee')" class="mt-2" />
                                            </div>
                                        </div>
                                        
                                        {{-- Dietary Supplement 2025 --}}
                                        <div>
                                            <label for="dietary_supplement_enabled" class="inline-flex items-center mb-2">
                                                <input id="dietary_supplement_enabled" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="dietary_supplement_enabled" value="1" {{ old('dietary_supplement_enabled') ? 'checked' : '' }}>
                                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Dietary Supplement Available (2025)') }}</span>
                                            </label>
                                            <x-input-error :messages="$errors->get('dietary_supplement_enabled')" class="mt-2" />
                                            
                                            <div>
                                                <x-input-label for="dietary_supplement_fee" :value="__('Dietary Supplement Fee (2025)')" />
                                                <x-text-input id="dietary_supplement_fee" class="block mt-1 w-full" type="number" step="0.01" name="dietary_supplement_fee" :value="old('dietary_supplement_fee', 0)" />
                                                <x-input-error :messages="$errors->get('dietary_supplement_fee')" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2026 Add-on Fees --}}
                                <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                                    <h4 class="text-md font-semibold text-blue-800 dark:text-blue-200 mb-3">2026 Add-on Fees</h4>
                                    
                                    <div class="space-y-4">
                                        {{-- Private Bathroom 2026 --}}
                                        <div>
                                            <label for="private_bathroom_enabled_2026" class="inline-flex items-center mb-2">
                                                <input id="private_bathroom_enabled_2026" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="private_bathroom_enabled_2026" value="1" {{ old('private_bathroom_enabled_2026') ? 'checked' : '' }}>
                                                <span class="ms-2 text-sm text-blue-600 dark:text-blue-400">{{ __('Private Bathroom Available (2026)') }}</span>
                                            </label>
                                            <x-input-error :messages="$errors->get('private_bathroom_enabled_2026')" class="mt-2" />
                                            
                                            <div>
                                                <x-input-label for="private_bathroom_fee_2026" :value="__('Private Bathroom Fee (2026)')" />
                                                <x-text-input id="private_bathroom_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="private_bathroom_fee_2026" :value="old('private_bathroom_fee_2026', 0)" />
                                                <x-input-error :messages="$errors->get('private_bathroom_fee_2026')" class="mt-2" />
                                            </div>
                                        </div>
                                        
                                        {{-- Dietary Supplement 2026 --}}
                                        <div>
                                            <label for="dietary_supplement_enabled_2026" class="inline-flex items-center mb-2">
                                                <input id="dietary_supplement_enabled_2026" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="dietary_supplement_enabled_2026" value="1" {{ old('dietary_supplement_enabled_2026') ? 'checked' : '' }}>
                                                <span class="ms-2 text-sm text-blue-600 dark:text-blue-400">{{ __('Dietary Supplement Available (2026)') }}</span>
                                            </label>
                                            <x-input-error :messages="$errors->get('dietary_supplement_enabled_2026')" class="mt-2" />
                                            
                                            <div>
                                                <x-input-label for="dietary_supplement_fee_2026" :value="__('Dietary Supplement Fee (2026)')" />
                                                <x-text-input id="dietary_supplement_fee_2026" class="block mt-1 w-full" type="number" step="0.01" name="dietary_supplement_fee_2026" :value="old('dietary_supplement_fee_2026', 0)" />
                                                <x-input-error :messages="$errors->get('dietary_supplement_fee_2026')" class="mt-2" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Other Charges Section --}}
                        <div class="mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Other charges</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <label for="other_charge_enabled" class="inline-flex items-center">
                                        <input id="other_charge_enabled" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="other_charge_enabled" value="1" {{ old('other_charge_enabled') ? 'checked' : '' }}>
                                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Enable other one-time charge') }}</span>
                                    </label>
                                    <x-input-error :messages="$errors->get('other_charge_enabled')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="other_charge_name" :value="__('Charge name shown to users')" />
                                    <x-text-input id="other_charge_name" class="block mt-1 w-full" type="text" name="other_charge_name" :value="old('other_charge_name', 'Non-refundable lease fee (per booking)')" />
                                    <x-input-error :messages="$errors->get('other_charge_name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="other_charge_amount" :value="__('Fixed amount')" />
                                    <x-text-input id="other_charge_amount" class="block mt-1 w-full" type="number" step="0.01" min="0" name="other_charge_amount" :value="old('other_charge_amount')" />
                                    <p class="text-sm text-gray-500 mt-1">Applied once per booking when enabled, not per week.</p>
                                    <x-input-error :messages="$errors->get('other_charge_amount')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        {{-- Active Checkbox --}}
                        <div class="block mt-6 border-t pt-4 border-gray-200 dark:border-gray-700">
                            <label for="active" class="inline-flex items-center">
                                <input id="active" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                            </label>
                             <x-input-error :messages="$errors->get('active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('admin.accommodations.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button>
                                {{ __('Save Accommodation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
