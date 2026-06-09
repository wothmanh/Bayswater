@extends('layouts.app')

@section('content')
@php($activeSection = $activeSection ?? 'system')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-6">{{ $activeSection === 'market-discount' ? 'Market discount' : 'System Settings' }}</h2>

                @if(session('status'))
                    <div class="mb-4 text-green-600">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_settings_section" value="{{ $activeSection }}">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Logo Section --}}
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-lg font-semibold text-bayswater-blue mb-4">Company Logo</h3>
                            <div class="flex items-start space-x-6">
                                <div class="w-40 h-40 bg-gray-100 flex items-center justify-center rounded border">
                                    @if($settings->logo_path)
                                        <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Company Logo" class="max-w-full max-h-full p-2">
                                    @else
                                        <div class="text-gray-400 text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <p class="text-sm">No logo uploaded</p>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label for="logo" class="block text-sm font-medium text-gray-700">Upload Logo</label>
                                    <p class="text-xs text-gray-500 mb-2">Recommended size: 200x80px. Max file size: 2MB.</p>
                                    <input type="file" id="logo" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-bayswater-blue file:text-white hover:file:bg-bayswater-blue-dark">
                                    
                                    @if($settings->logo_path)
                                        <div class="mt-2">
                                            <a href="{{ route('admin.settings.remove-logo') }}" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Are you sure you want to remove the logo?')">Remove Logo</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Favicon Section --}}
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-lg font-semibold text-bayswater-blue mb-4">Website Favicon</h3>
                            <div class="flex items-start space-x-6">
                                <div class="w-16 h-16 bg-gray-100 flex items-center justify-center rounded border">
                                    @if($settings->favicon_path)
                                        <img src="{{ asset('storage/' . $settings->favicon_path) }}" alt="Favicon" class="max-w-full max-h-full p-1">
                                    @else
                                        <div class="text-gray-400 text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                            </svg>
                                            <p class="text-xs">No favicon</p>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label for="favicon" class="block text-sm font-medium text-gray-700">Upload Favicon</label>
                                    <p class="text-xs text-gray-500 mb-2">Recommended size: 16x16px or 32x32px. Formats: .ico, .png, .jpg, .svg. Max file size: 512KB.</p>
                                    <input type="file" id="favicon" name="favicon" accept=".ico,.png,.jpg,.jpeg,.svg" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-bayswater-blue file:text-white hover:file:bg-bayswater-blue-dark">
                                    <x-input-error :messages="$errors->get('favicon')" class="mt-2" />
                                    
                                    @if($settings->favicon_path)
                                        <div class="mt-2">
                                            <span class="text-green-600 text-sm">Current: {{ basename($settings->favicon_path) }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Company Information --}}
                        <div>
                            <h3 class="text-lg font-semibold text-bayswater-blue mb-4">Company Information</h3>
                            
                            <div class="mb-4">
                                <x-input-label for="company_name" :value="__('Company Name')" />
                                <x-text-input id="company_name" class="block mt-1 w-full" type="text" name="company_name" :value="old('company_name', $settings->company_name)" />
                                <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
                            </div>
                            
                            <div class="mb-4">
                                <x-input-label for="company_email" :value="__('Company Email')" />
                                <x-text-input id="company_email" class="block mt-1 w-full" type="email" name="company_email" :value="old('company_email', $settings->company_email)" />
                                <x-input-error :messages="$errors->get('company_email')" class="mt-2" />
                            </div>
                            
                            <div class="mb-4">
                                <x-input-label for="company_phone" :value="__('Company Phone')" />
                                <x-text-input id="company_phone" class="block mt-1 w-full" type="text" name="company_phone" :value="old('company_phone', $settings->company_phone)" />
                                <x-input-error :messages="$errors->get('company_phone')" class="mt-2" />
                            </div>
                            
                            <div class="mb-4">
                                <x-input-label for="cutoff_date" :value="__('Pricing Cutoff Date')" />
                                <x-text-input id="cutoff_date" class="block mt-1 w-full" type="date" name="cutoff_date" :value="old('cutoff_date', $settings->cutoff_date)" />
                                <x-input-error :messages="$errors->get('cutoff_date')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">Set the date when pricing switches from 2025 to 2026 rates.</p>
                            </div>
                            
                            <div class="mb-4">
                                <x-input-label for="quotation_extraction_date" :value="__('Quotation Extraction Date Override')" />
                                <x-text-input id="quotation_extraction_date" class="block mt-1 w-full" type="date" name="quotation_extraction_date" :value="old('quotation_extraction_date', $settings->quotation_extraction_date)" />
                                <x-input-error :messages="$errors->get('quotation_extraction_date')" class="mt-2" />
                                <p class="text-xs text-gray-500 mt-1">Override today's date for testing pricing rules. Leave empty to use actual system date.</p>
                                @if($settings->quotation_extraction_date)
                                    <div class="mt-2">
                                        <button type="button" id="clear-quotation-date" class="text-red-600 hover:text-red-800 text-sm">Clear Override</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold text-bayswater-blue mb-4">Address</h3>
                            
                            <div class="mb-4">
                                <x-input-label for="company_address" :value="__('Company Address')" />
                                <textarea id="company_address" name="company_address" rows="5" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('company_address', $settings->company_address) }}</textarea>
                                <x-input-error :messages="$errors->get('company_address')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Course Details Button Settings --}}
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-4">Course Details Button</h3>
                        <p class="text-sm text-gray-600 mb-4">Configure the label text for the "Course Details" buttons in the calculator. Default is "Course details".</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="course_details_button_text" class="block text-sm font-medium text-gray-700">Button Text</label>
                                <input type="text" id="course_details_button_text" name="course_details_button_text" value="{{ old('course_details_button_text', $settings->course_details_button_text ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Course details" />
                                @error('course_details_button_text')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- WhatsApp Chat Settings --}}
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-4">WhatsApp Chat Settings</h3>
                        <p class="text-sm text-gray-600 mb-4">Configure the WhatsApp chat icon. It appears site-wide only when both the number and default message are filled.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700">WhatsApp Number</label>
                                <input type="text" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="35799157578" />
                                @error('whatsapp_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="whatsapp_default_message" class="block text-sm font-medium text-gray-700">Default Message</label>
                                <input type="text" id="whatsapp_default_message" name="whatsapp_default_message" value="{{ old('whatsapp_default_message', $settings->whatsapp_default_message ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Whatsapp Admissions Team" />
                                @error('whatsapp_default_message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Leave either field blank to hide the icon.</p>
                    </div>

                    {{-- Search Accommodation Settings --}}
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-4">Search Accommodation</h3>
                        <p class="text-sm text-gray-600 mb-4">Configure the external link for the "Search Accommodation" sidebar button. The button appears only when both fields are filled.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="search_accommodation_tab_title" class="block text-sm font-medium text-gray-700">Tab Title</label>
                                <input type="text" id="search_accommodation_tab_title" name="search_accommodation_tab_title" value="{{ old('search_accommodation_tab_title', $settings->search_accommodation_tab_title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Search Accommodation" />
                                @error('search_accommodation_tab_title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="search_accommodation_page_link" class="block text-sm font-medium text-gray-700">Page Link</label>
                                <input type="url" id="search_accommodation_page_link" name="search_accommodation_page_link" value="{{ old('search_accommodation_page_link', $settings->search_accommodation_page_link ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com/accommodation" />
                                @error('search_accommodation_page_link')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Partner Zone Settings --}}
                    <div class="border-t pt-6">
                        <h3 class="text-xl font-semibold mb-4">Partner Zone</h3>
                        <p class="text-sm text-gray-600 mb-4">Configure the external link for the "Partner Zone" sidebar button. The button appears only when both fields are filled.</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="partner_zone_tab_title" class="block text-sm font-medium text-gray-700">Tab Title</label>
                                <input type="text" id="partner_zone_tab_title" name="partner_zone_tab_title" value="{{ old('partner_zone_tab_title', $settings->partner_zone_tab_title ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Partner Zone" />
                                @error('partner_zone_tab_title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="partner_zone_page_link" class="block text-sm font-medium text-gray-700">Page Link</label>
                                <input type="url" id="partner_zone_page_link" name="partner_zone_page_link" value="{{ old('partner_zone_page_link', $settings->partner_zone_page_link ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com/partner-zone" />
                                @error('partner_zone_page_link')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-bayswater-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const faviconInput = document.getElementById('favicon');
        
        faviconInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Check file size (512KB = 524288 bytes)
                if (file.size > 524288) {
                    alert('File size must be less than 512KB');
                    e.target.value = '';
                    return;
                }
                
                // Check file type
                const allowedTypes = ['image/x-icon', 'image/vnd.microsoft.icon', 'image/png', 'image/jpeg', 'image/jpg', 'image/svg+xml'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only .ico, .png, .jpg, and .svg files are allowed');
                    e.target.value = '';
                    return;
                }
            }
        });
        
        // Clear quotation extraction date override
        const clearQuotationDateBtn = document.getElementById('clear-quotation-date');
        if (clearQuotationDateBtn) {
            clearQuotationDateBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to clear the quotation extraction date override?')) {
                    document.getElementById('quotation_extraction_date').value = '';
                }
            });
        }
    });
</script>