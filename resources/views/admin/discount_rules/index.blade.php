<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Discount Rules') }}
            </h2>
            <a href="{{ route('admin.discount-rules.create') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Add New Discount Rule') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Filter Form --}}
                    <div class="mb-6 p-6 rounded-lg shadow-lg" style="background-color: #283040;">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-medium text-gray-400 uppercase tracking-wider">Filters</h3>
                            @if(request()->hasAny(['name', 'applies_to', 'discount_type', 'school_id', 'course_type_id', 'course_id', 'region_id', 'active', 'date_condition_type', 'valid_from_date', 'valid_to_date']))
                                <a href="{{ route('admin.discount-rules.index') }}"
                                    class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                                    Clear All Filters
                                </a>
                            @endif
                        </div>
                        <form action="{{ route('admin.discount-rules.index') }}" method="GET">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Rule
                                        Name</label>
                                    <input type="text" name="name" id="name" value="{{ request('name') }}"
                                        placeholder="Search by name"
                                        class="mt-1 block w-full rounded-md filter-input-dark placeholder-gray-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                {{-- Applies To --}}
                                <div>
                                    <label for="applies_to" class="block text-sm font-medium text-gray-300 mb-1">Applies
                                        To</label>
                                    <select name="applies_to" id="applies_to"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All</option>
                                        @foreach($appliesToOptions as $key => $label)
                                            <option value="{{ $key }}" {{ request('applies_to') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Discount Type --}}
                                <div>
                                    <label for="discount_type"
                                        class="block text-sm font-medium text-gray-300 mb-1">Discount Type</label>
                                    <select name="discount_type" id="discount_type"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All</option>
                                        @foreach($discountTypes as $key => $label)
                                            <option value="{{ $key }}" {{ request('discount_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- School --}}
                                <div>
                                    <label for="school_id"
                                        class="block text-sm font-medium text-gray-300 mb-1">School</label>
                                    <select name="school_id" id="school_id"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All Schools</option>
                                        @foreach($schools as $id => $name)
                                            <option value="{{ $id }}" {{ request('school_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Course Type --}}
                                <div>
                                    <label for="course_type_id"
                                        class="block text-sm font-medium text-gray-300 mb-1">Course Type</label>
                                    <select name="course_type_id" id="course_type_id"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All Course Types</option>
                                        @foreach($courseTypes as $id => $name)
                                            <option value="{{ $id }}" {{ request('course_type_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Course --}}
                                <div>
                                    <label for="course_id"
                                        class="block text-sm font-medium text-gray-300 mb-1">Course</label>
                                    <select name="course_id" id="course_id"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All Courses</option>
                                        @foreach($courses as $id => $name)
                                            <option value="{{ $id }}" {{ request('course_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Region --}}
                                <div>
                                    <label for="region_id"
                                        class="block text-sm font-medium text-gray-300 mb-1">Region</label>
                                    <select name="region_id" id="region_id"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All Regions</option>
                                        @foreach($regions as $id => $name)
                                            <option value="{{ $id }}" {{ request('region_id') == $id ? 'selected' : '' }}>
                                                {{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Active Status --}}
                                <div>
                                    <label for="active" class="block text-sm font-medium text-gray-300 mb-1">Active
                                        Status</label>
                                    <select name="active" id="active"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All</option>
                                        <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inactive
                                        </option>
                                    </select>
                                </div>

                                {{-- Date Condition Type --}}
                                <div>
                                    <label for="date_condition_type"
                                        class="block text-sm font-medium text-gray-300 mb-1">Date Condition</label>
                                    <select name="date_condition_type" id="date_condition_type"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">All</option>
                                        @foreach($dateConditionTypes as $key => $label)
                                            <option value="{{ $key }}" {{ request('date_condition_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Valid From --}}
                                <div>
                                    <label for="valid_from_date"
                                        class="block text-sm font-medium text-gray-300 mb-1">Valid From</label>
                                    <input type="date" name="valid_from_date" id="valid_from_date"
                                        value="{{ request('valid_from_date') }}" placeholder="dd/mm/yyyy"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                {{-- Valid To --}}
                                <div>
                                    <label for="valid_to_date"
                                        class="block text-sm font-medium text-gray-300 mb-1">Valid To</label>
                                    <input type="date" name="valid_to_date" id="valid_to_date"
                                        value="{{ request('valid_to_date') }}" placeholder="dd/mm/yyyy"
                                        class="mt-1 block w-full rounded-md filter-input-dark focus:ring-1 focus:ring-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- TODO: Add Success/Error Messages --}}

                    <div class="overflow-x-auto rounded-lg shadow-lg">
                        <table class="min-w-full divide-y divide-gray-700">
                            <thead class="bg-[#283040]">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Name</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Type</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Value</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Applies To</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Priority</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Combinable</th>
                                    <th scope="col"
                                        class="px-6 py-4 text-left text-xs font-bold text-gray-300 uppercase tracking-wider">
                                        Active</th>
                                    <th scope="col" class="relative px-6 py-4">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-[#1F2937] divide-y divide-gray-700">
                                @forelse ($discountRules as $rule)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $rule->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ Str::title(str_replace('_', ' ', $rule->discount_type)) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            @if($rule->discount_type == 'percentage')
                                                {{ $rule->discount_value }}%
                                            @elseif($rule->discount_type == 'fixed_amount')
                                                {{ number_format($rule->discount_value, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ Str::title(str_replace('_', ' ', $rule->applies_to)) }}
                                            @if($rule->applies_to == 'addon')
                                                ({{ $rule->addon->name ?? 'N/A' }})
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            {{ $rule->priority }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            @if($rule->combinable) Yes @else No @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            @if($rule->active)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Yes</span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">No</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.discount-rules.edit', $rule) }}"
                                                class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-3">Edit</a>
                                            <form action="{{ route('admin.discount-rules.destroy', $rule) }}" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Are you sure you want to delete this discount rule?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
                                            No discount rules found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $discountRules->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterForm = document.querySelector('form[action="{{ route('admin.discount-rules.index') }}"]');

            if (filterForm) {
                // Get all filter inputs
                const filterInputs = filterForm.querySelectorAll('select, input[type="date"]');

                // Add change event listener to all filter inputs
                filterInputs.forEach(input => {
                    input.addEventListener('change', function () {
                        filterForm.submit();
                    });
                });

                // For text input (name), submit on Enter key
                const nameInput = filterForm.querySelector('input[name="name"]');
                if (nameInput) {
                    nameInput.addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            filterForm.submit();
                        }
                    });
                }
            }
        });
    </script>

    <style>
        /* Force dark theme for discount rules page specific elements matches screenshot */
        .filter-input-dark {
            background-color: #1F2937 !important;
            border-color: transparent !important;
            color: #e5e7eb !important;
        }

        .filter-input-dark:focus {
            background-color: #1F2937 !important;
            border-color: #6366f1 !important;
            /* Indigo-500 */
            ring-color: #6366f1 !important;
        }

        /* Table header dark */
        .min-w-full thead.bg-\[\#283040\] {
            background-color: #283040 !important;
        }
    </style>
</x-app-layout>