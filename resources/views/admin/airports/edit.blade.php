<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Edit Airport') }}: {{ $airport->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.airports.update', $airport) }}">
                        @csrf
                        @method('PUT') {{-- Method spoofing for update --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Airport Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $airport->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- School --}}
                            <div>
                                <x-input-label for="school_id" :value="__('Associated School')" />
                                <select name="school_id" id="school_id" class="block mt-1 w-full border-gray-300 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm" required>
                                    <option value="">-- Select School --</option>
                                    @foreach($schools as $id => $name)
                                        <option value="{{ $id }}" {{ old('school_id', $airport->school_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                            </div>

                            {{-- Allowed Course Types (Restriction) --}}
                            <div>
                                <x-input-label for="restricted_course_type_ids" :value="__('Allowed Course Types (Optional - Leave empty for all)')" />
                                <select name="restricted_course_type_ids[]" id="restricted_course_type_ids" multiple class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm h-32">
                                    @foreach($courseTypes as $type)
                                        <option value="{{ $type->id }}" {{ in_array($type->id, old('restricted_course_type_ids', $airport->restrictedCourseTypes->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</p>
                                <x-input-error :messages="$errors->get('restricted_course_type_ids')" class="mt-2" />
                            </div>

                            {{-- Allowed Courses (Restriction) --}}
                            <div>
                                <x-input-label for="restricted_course_ids" :value="__('Allowed Courses (Optional - Leave empty for all)')" />
                                <select name="restricted_course_ids[]" id="restricted_course_ids" multiple class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm h-32">
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" 
                                            data-school-id="{{ $course->school_id }}" 
                                            data-course-type-id="{{ $course->course_type_id }}"
                                            {{ in_array($course->id, old('restricted_course_ids', $airport->restrictedCourses->pluck('id')->toArray())) ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</p>
                                <x-input-error :messages="$errors->get('restricted_course_ids')" class="mt-2" />
                            </div>

                            {{-- Arrival Price (2025) --}}
                            <div>
                                <x-input-label for="arrival_price" :value="__('Arrival Transfer Price (2025)')" />
                                <x-text-input id="arrival_price" class="block mt-1 w-full" type="number" step="0.01" name="arrival_price" :value="old('arrival_price', $airport->arrival_price)" />
                                <x-input-error :messages="$errors->get('arrival_price')" class="mt-2" />
                            </div>

                            {{-- Arrival Price (2026) --}}
                            <div>
                                <x-input-label for="arrival_price_2026" :value="__('Arrival Transfer Price (2026)')" />
                                <x-text-input id="arrival_price_2026" class="block mt-1 w-full" type="number" step="0.01" name="arrival_price_2026" :value="old('arrival_price_2026', $airport->arrival_price_2026)" />
                                <x-input-error :messages="$errors->get('arrival_price_2026')" class="mt-2" />
                            </div>

                            {{-- Departure Price (2025) --}}
                            <div>
                                <x-input-label for="departure_price" :value="__('Departure Transfer Price (2025)')" />
                                <x-text-input id="departure_price" class="block mt-1 w-full" type="number" step="0.01" name="departure_price" :value="old('departure_price', $airport->departure_price)" />
                                <x-input-error :messages="$errors->get('departure_price')" class="mt-2" />
                            </div>

                            {{-- Departure Price (2026) --}}
                            <div>
                                <x-input-label for="departure_price_2026" :value="__('Departure Transfer Price (2026)')" />
                                <x-text-input id="departure_price_2026" class="block mt-1 w-full" type="number" step="0.01" name="departure_price_2026" :value="old('departure_price_2026', $airport->departure_price_2026)" />
                                <x-input-error :messages="$errors->get('departure_price_2026')" class="mt-2" />
                            </div>

                             {{-- Active Status --}}
                             <div class="md:col-span-2">
                                 <label for="active" class="inline-flex items-center">
                                     <input id="active" type="checkbox" class="rounded border-gray-300 text-bayswater-blue shadow-sm focus:ring-bayswater-blue" name="active" value="1" {{ old('active', $airport->active) ? 'checked' : '' }}>
                                     <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                 </label>
                             </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.airports.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Airport') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const schoolSelect = document.getElementById('school_id');
            const courseTypeSelect = document.getElementById('restricted_course_type_ids');
            const courseSelect = document.getElementById('restricted_course_ids');
            
            // Store original options for course select to reuse them
            const allCourseOptions = Array.from(courseSelect.options);
            
            function filterCourses() {
                const selectedSchoolId = schoolSelect.value;
                // Handle multi-select for course types
                const selectedCourseTypeIds = Array.from(courseTypeSelect.selectedOptions).map(opt => opt.value);
                
                // Clear current options
                courseSelect.innerHTML = '';
                
                allCourseOptions.forEach(option => {
                    const schoolId = option.getAttribute('data-school-id');
                    const courseTypeId = option.getAttribute('data-course-type-id');
                    
                    // Filter by School
                    // Only show courses that belong to the selected school
                    if (selectedSchoolId && schoolId !== selectedSchoolId) {
                        return; 
                    }
                    
                    // Filter by Course Type (if any selected)
                    if (selectedCourseTypeIds.length > 0) {
                        if (!selectedCourseTypeIds.includes(courseTypeId)) {
                            return;
                        }
                    }
                    
                    // If we get here, show the option
                    courseSelect.appendChild(option);
                });
            }
            
            schoolSelect.addEventListener('change', filterCourses);
            courseTypeSelect.addEventListener('change', filterCourses);
            
            // Initial filter
            filterCourses();
        });
    </script>
</x-app-layout>
