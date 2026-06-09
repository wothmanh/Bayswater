<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Accommodations') }}
            </h2>
            <a href="{{ route('admin.accommodations.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Add New Accommodation') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- TODO: Add Success/Error Messages --}}
                    {{-- Filters: Country, City, School --}}
                    <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="filter-country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Country</label>
                            <select id="filter-country" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Countries</option>
                                @isset($countries)
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div>
                            <label for="filter-city" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by City</label>
                            <select id="filter-city" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Cities</option>
                                @isset($cities)
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div>
                            <label for="filter-school" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by School</label>
                            <select id="filter-school" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">All Schools</option>
                                @isset($schools)
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">School</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Room Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Meal Plan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortable-accommodations" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @include('admin.accommodations._index_rows')
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div id="pagination-container" class="mt-4">
                        @include('admin.accommodations._pagination')
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- SortableJS is bundled via Vite in resources/js/app.js --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const countrySelect = document.getElementById('filter-country');
            const citySelect = document.getElementById('filter-city');
            const schoolSelect = document.getElementById('filter-school');
            const rowsContainer = document.getElementById('sortable-accommodations');
            const paginationContainer = document.getElementById('pagination-container');

            function initSortable() {
                if (rowsContainer && !rowsContainer.dataset.sortableInit) {
                    const sortable = Sortable.create(rowsContainer, {
                        handle: '.drag-handle',
                        animation: 150,
                        onEnd: function(evt) {
                            const rows = rowsContainer.querySelectorAll('tr[data-id]');
                            const orderData = [];
                            rows.forEach((row, index) => {
                                orderData.push({ id: row.getAttribute('data-id'), order: index + 1 });
                            });
                            fetch('{{ route("admin.accommodations.update-order") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ accommodations: orderData })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (!data.success) {
                                    console.error('Failed to update order');
                                }
                            })
                            .catch(error => {
                                console.error('Error updating order:', error);
                                alert('Failed to update order. Please try again.');
                            });
                        }
                    });
                    rowsContainer.dataset.sortableInit = 'true';
                }
            }

            function fetchCities(countryId) {
                const url = new URL('{{ route("admin.accommodations.cities-for-country") }}', window.location.origin);
                if (countryId) url.searchParams.append('country_id', countryId);
                return fetch(url)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.innerHTML = '<option value="">All Cities</option>';
                        cities.forEach(city => {
                            const opt = document.createElement('option');
                            opt.value = city.id;
                            opt.textContent = city.name;
                            citySelect.appendChild(opt);
                        });
                    });
            }

            function fetchSchools(countryId, cityId) {
                const url = new URL('{{ route("admin.accommodations.schools-for-country-city") }}', window.location.origin);
                if (countryId) url.searchParams.append('country_id', countryId);
                if (cityId) url.searchParams.append('city_id', cityId);
                return fetch(url)
                    .then(response => response.json())
                    .then(schools => {
                        schoolSelect.innerHTML = '<option value="">All Schools</option>';
                        schools.forEach(school => {
                            const opt = document.createElement('option');
                            opt.value = school.id;
                            opt.textContent = school.name;
                            schoolSelect.appendChild(opt);
                        });
                    });
            }

            function fetchAccommodations(pageUrl = null) {
                const baseUrl = pageUrl ? new URL(pageUrl, window.location.origin) : new URL('{{ route("admin.accommodations.filter") }}', window.location.origin);
                const countryId = countrySelect.value;
                const cityId = citySelect.value;
                const schoolId = schoolSelect.value;
                if (countryId) baseUrl.searchParams.set('country_id', countryId);
                if (cityId) baseUrl.searchParams.set('city_id', cityId);
                if (schoolId) baseUrl.searchParams.set('school_id', schoolId);

                fetch(baseUrl)
                    .then(response => response.json())
                    .then(data => {
                        rowsContainer.innerHTML = data.rows;
                        paginationContainer.innerHTML = data.pagination;
                        // re-init sortable after DOM update
                        rowsContainer.dataset.sortableInit = '';
                        initSortable();
                        attachPaginationHandlers();
                    })
                    .catch(error => console.error('Error fetching accommodations:', error));
            }

            function attachPaginationHandlers() {
                paginationContainer.querySelectorAll('a').forEach(anchor => {
                    anchor.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = this.getAttribute('href');
                        if (url) fetchAccommodations(url);
                    });
                });
            }

            countrySelect.addEventListener('change', function() {
                const countryId = this.value;
                if (!countryId) {
                    citySelect.innerHTML = '<option value="">All Cities</option>';
                    schoolSelect.innerHTML = '<option value="">All Schools</option>';
                    fetchAccommodations();
                    return;
                }
                fetchCities(countryId).then(() => fetchSchools(countryId, null)).then(fetchAccommodations);
            });

            citySelect.addEventListener('change', function() {
                const cityId = this.value;
                const countryId = countrySelect.value;
                if (!cityId) {
                    fetchSchools(countryId || null, null).then(fetchAccommodations);
                    return;
                }
                fetchSchools(countryId || null, cityId).then(fetchAccommodations);
            });

            schoolSelect.addEventListener('change', function() {
                fetchAccommodations();
            });

            // Initialize sortable and pagination handlers
            initSortable();
            attachPaginationHandlers();

            // Optional: refresh list when clearing selectors
            ['filter-country', 'filter-city', 'filter-school'].forEach(id => {
                const el = document.getElementById(id);
                el && el.addEventListener('blur', function() {
                    if (this.value === '') fetchAccommodations();
                });
            });
        });
    </script>

    <style>
        .drag-handle { cursor: grab; user-select: none; }
        .drag-handle:active { cursor: grabbing; }
        .sortable-row:hover { background-color: rgba(0, 0, 0, 0.05); }
    </style>
</x-app-layout>
