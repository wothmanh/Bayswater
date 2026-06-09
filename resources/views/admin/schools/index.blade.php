<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Schools') }}
            </h2>
            <a href="{{ route('admin.schools.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Add New School') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- TODO: Add Success/Error Messages --}}

                    {{-- Filters --}}
                    <div class="mb-4 flex gap-4 items-end">
                        <div>
                            <label for="filter-country" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Country</label>
                            <select id="filter-country" class="mt-1 block w-64 pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
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
                            <select id="filter-city" class="mt-1 block w-64 pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Cities</option>
                                @isset($cities)
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">School Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">City</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Country</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Currency</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortable-schools" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @include('admin.schools._index_rows', ['schools' => $schools])
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4" id="schools-pagination">
                        @include('admin.schools._pagination', ['schools' => $schools])
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
            const tbodyEl = document.getElementById('sortable-schools');
            const paginationEl = document.getElementById('schools-pagination');
            // Abort controller to prevent stale AJAX overwrites
            let filtersAbortController = null;
            // Request ordering guard to ignore out-of-date responses
            let latestRequestId = 0;
            // Event sequence guard to prevent earlier country updates overriding later city selection
            let filtersEventSeq = 0;
            // Debounce timer to delay country-only update
            let countryUpdateTimeout = null;

            function initSortable() {
                const sortableElement = document.getElementById('sortable-schools');
                if (sortableElement) {
                    const sortable = Sortable.create(sortableElement, {
                        handle: '.drag-handle',
                        animation: 150,
                        onEnd: function(evt) {
                            const rows = sortableElement.querySelectorAll('.sortable-row');
                            const schools = [];
                            rows.forEach((row, index) => {
                                schools.push({ id: row.dataset.id, order: index });
                            });
                            fetch('{{ route("admin.schools.update-order") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ schools: schools })
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    rows.forEach((row, index) => {
                                        const orderSpan = row.querySelector('td:first-child span:last-child');
                                        if (orderSpan) {
                                            orderSpan.textContent = index;
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error updating order:', error);
                                alert('Failed to update order. Please try again.');
                            });
                        }
                    });
                }
            }

            function fetchCitiesForCountry(countryId) {
                const url = new URL('{{ route('admin.schools.cities-for-country') }}', window.location.origin);
                if (countryId) {
                    url.searchParams.set('country_id', countryId);
                }
                return fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                }).then(r => r.json());
            }

            function fetchFilteredSchools(countryId, cityId, page = null, signal = null) {
                const url = new URL('{{ route('admin.schools.filter') }}', window.location.origin);
                if (countryId) url.searchParams.set('country_id', countryId);
                if (cityId) url.searchParams.set('city_id', cityId);
                if (page) url.searchParams.set('page', page);
                return fetch(url.toString(), {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal
                }).then(r => r.json());
            }

            function bindPaginationLinks(countryId, cityId) {
                const links = paginationEl.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        const page = url.searchParams.get('page');
                        // Abort any in-flight filter requests before issuing a new one
                        if (filtersAbortController) {
                            try { filtersAbortController.abort(); } catch (_) {}
                        }
                        filtersAbortController = new AbortController();
                        const signal = filtersAbortController.signal;
                        const reqId = ++latestRequestId;
                        fetchFilteredSchools(countryId, cityId, page, signal)
                            .then(data => {
                                if (reqId !== latestRequestId) { return; }
                                if (data && typeof data.rowsHtml === 'string') {
                                    tbodyEl.innerHTML = data.rowsHtml;
                                }
                                if (data && typeof data.paginationHtml === 'string') {
                                    paginationEl.innerHTML = data.paginationHtml;
                                }
                                initSortable();
                                bindPaginationLinks(countryId, cityId);
                            })
                            .catch(err => {
                                if (err && err.name === 'AbortError') { return; }
                                console.error('Pagination fetch error:', err);
                            });
                    });
                });
            }

            function updateSchools(countryId, cityId) {
                // Abort any in-flight filter requests before issuing a new one
                if (filtersAbortController) {
                    try { filtersAbortController.abort(); } catch (_) {}
                }
                filtersAbortController = new AbortController();
                const signal = filtersAbortController.signal;
                const reqId = ++latestRequestId;
                fetchFilteredSchools(countryId, cityId, null, signal)
                    .then(data => {
                        if (reqId !== latestRequestId) { return; }
                        if (data && typeof data.rowsHtml === 'string') {
                            tbodyEl.innerHTML = data.rowsHtml;
                        }
                        if (data && typeof data.paginationHtml === 'string') {
                            paginationEl.innerHTML = data.paginationHtml;
                        }
                        initSortable();
                        bindPaginationLinks(countryId, cityId);
                    })
                    .catch(err => {
                        if (err && err.name === 'AbortError') { return; }
                        console.error('Filter fetch error:', err);
                    });
            }

            countrySelect.addEventListener('change', function() {
                const selectedCountryId = this.value;
                const seq = ++filtersEventSeq;
                const prevCityValue = citySelect.value;
                fetchCitiesForCountry(selectedCountryId)
                    .then(cities => {
                        citySelect.innerHTML = '<option value="">All Cities</option>';
                        cities.forEach(city => {
                            const opt = document.createElement('option');
                            opt.value = city.id;
                            opt.textContent = city.name;
                            citySelect.appendChild(opt);
                        });
                        // Try to preserve previously selected city if still valid
                        const hasPrevCity = Array.from(citySelect.options).some(opt => opt.value == prevCityValue);
                        citySelect.value = hasPrevCity ? prevCityValue : '';
                        // Debounce country-only update; cancel if a city gets selected soon
                        if (countryUpdateTimeout) { clearTimeout(countryUpdateTimeout); }
                        countryUpdateTimeout = setTimeout(() => {
                            // Only perform country-only update if no newer filter event occurred
                            if (seq === filtersEventSeq && citySelect.value === '') {
                                updateSchools(selectedCountryId, '');
                            }
                        }, 200);
                    })
                    .catch(err => console.error('Cities fetch error:', err));
            });

            citySelect.addEventListener('change', function() {
                const selectedCityId = this.value;
                const selectedCountryId = countrySelect.value;
                // Bump event sequence so any pending country-only update is ignored
                filtersEventSeq++;
                // Cancel any pending country-only debounce
                if (countryUpdateTimeout) { clearTimeout(countryUpdateTimeout); }
                updateSchools(selectedCountryId, selectedCityId);
            });

            initSortable();
            bindPaginationLinks('', '');

            countrySelect.addEventListener('blur', function() {
                if (this.value === '') {
                    fetchCitiesForCountry('')
                        .then(cities => {
                            citySelect.innerHTML = '<option value="">All Cities</option>';
                            cities.forEach(city => {
                                const opt = document.createElement('option');
                                opt.value = city.id;
                                opt.textContent = city.name;
                                citySelect.appendChild(opt);
                            });
                            citySelect.value = '';
                        });
                    updateSchools('', '');
                }
            });
            citySelect.addEventListener('blur', function() {
                if (this.value === '') {
                    updateSchools(countrySelect.value, '');
                }
            });
        });
    </script>

    <style>
        .drag-handle {
            cursor: grab;
            user-select: none;
        }
        .drag-handle:active {
            cursor: grabbing;
        }
        .sortable-row:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }
    </style>
</x-app-layout>
