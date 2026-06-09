<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Manage Airports') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-700">Airport List</h3>
                        <a href="{{ route('admin.airports.create') }}" class="bg-bayswater-blue hover:bg-bayswater-blue-dark text-white font-bold py-2 px-4 rounded">
                            Add New Airport
                        </a>
                    </div>

                    {{-- Filters --}}
                    <div class="mb-4 flex gap-4 items-end">
                        <div>
                            <label for="filter-country" class="block text-sm font-medium text-gray-700">Filter by Country</label>
                            <select id="filter-country" class="mt-1 block w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Countries</option>
                                @isset($countries)
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div>
                            <label for="filter-city" class="block text-sm font-medium text-gray-700">Filter by City</label>
                            <select id="filter-city" class="mt-1 block w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Cities</option>
                                @isset($cities)
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div>
                            <label for="filter-school" class="block text-sm font-medium text-gray-700">Filter by School</label>
                            <select id="filter-school" class="mt-1 block w-64 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Schools</option>
                                @isset($schools)
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>

                    {{-- Airport Table --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">School</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrival Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departure Price</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortable-airports" class="bg-white divide-y divide-gray-200">
                                @include('admin.airports._index_rows', ['airports' => $airports])
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4" id="airports-pagination">
                        @include('admin.airports._pagination', ['airports' => $airports])
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
            const tbodyEl = document.getElementById('sortable-airports');
            const paginationEl = document.getElementById('airports-pagination');
            let filtersAbortController = null;
            let latestRequestId = 0;
            let filtersEventSeq = 0;
            let countryUpdateTimeout = null;

            function initSortable() {
                const el = document.getElementById('sortable-airports');
                if (el) {
                    Sortable.create(el, {
                        handle: '.drag-handle',
                        animation: 150,
                        onEnd: function(evt) {
                            const rows = el.querySelectorAll('.sortable-row');
                            const airports = [];
                            rows.forEach((row, index) => {
                                airports.push({ id: row.dataset.id, order: index });
                            });
                            fetch('{{ route("admin.airports.update-order") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ airports })
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    rows.forEach((row, index) => {
                                        const orderSpan = row.querySelector('td:first-child div');
                                        if (orderSpan) {
                                            // Order is displayed, index starts from 0 here in display
                                        }
                                    });
                                }
                            })
                            .catch(err => console.error('Error updating order:', err));
                        }
                    });
                }
            }

            function fetchCitiesForCountry(countryId) {
                const url = new URL('{{ route('admin.airports.cities-for-country') }}', window.location.origin);
                if (countryId) url.searchParams.set('country_id', countryId);
                return fetch(url.toString(), { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(r => r.json());
            }

            function fetchSchools(countryId, cityId) {
                const url = new URL('{{ route('admin.airports.schools-for-country-city') }}', window.location.origin);
                if (countryId) url.searchParams.set('country_id', countryId);
                if (cityId) url.searchParams.set('city_id', cityId);
                return fetch(url.toString(), { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(r => r.json());
            }

            function fetchFilteredAirports(countryId, cityId, schoolId, page = null, signal = null) {
                const url = new URL('{{ route('admin.airports.filter') }}', window.location.origin);
                if (countryId) url.searchParams.set('country_id', countryId);
                if (cityId) url.searchParams.set('city_id', cityId);
                if (schoolId) url.searchParams.set('school_id', schoolId);
                if (page) url.searchParams.set('page', page);
                return fetch(url.toString(), { method: 'GET', headers: { 'Accept': 'application/json' }, credentials: 'same-origin', signal })
                    .then(r => r.json());
            }

            function bindPaginationLinks(countryId, cityId, schoolId) {
                const links = paginationEl.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        const page = url.searchParams.get('page');
                        if (filtersAbortController) { try { filtersAbortController.abort(); } catch (_) {} }
                        filtersAbortController = new AbortController();
                        const signal = filtersAbortController.signal;
                        const reqId = ++latestRequestId;
                        fetchFilteredAirports(countryId, cityId, schoolId, page, signal)
                            .then(data => {
                                if (reqId !== latestRequestId) return;
                                if (data && typeof data.rowsHtml === 'string') tbodyEl.innerHTML = data.rowsHtml;
                                if (data && typeof data.paginationHtml === 'string') paginationEl.innerHTML = data.paginationHtml;
                                initSortable();
                                bindPaginationLinks(countryId, cityId, schoolId);
                            })
                            .catch(err => { if (err && err.name === 'AbortError') return; console.error('Pagination fetch error:', err); });
                    });
                });
            }

            function updateAirports(countryId, cityId, schoolId) {
                if (filtersAbortController) { try { filtersAbortController.abort(); } catch (_) {} }
                filtersAbortController = new AbortController();
                const signal = filtersAbortController.signal;
                const reqId = ++latestRequestId;
                fetchFilteredAirports(countryId, cityId, schoolId, null, signal)
                    .then(data => {
                        if (reqId !== latestRequestId) return;
                        if (data && typeof data.rowsHtml === 'string') tbodyEl.innerHTML = data.rowsHtml;
                        if (data && typeof data.paginationHtml === 'string') paginationEl.innerHTML = data.paginationHtml;
                        initSortable();
                        bindPaginationLinks(countryId, cityId, schoolId);
                    })
                    .catch(err => { if (err && err.name === 'AbortError') return; console.error('Filter fetch error:', err); });
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
                        const hasPrevCity = Array.from(citySelect.options).some(opt => opt.value == prevCityValue);
                        citySelect.value = hasPrevCity ? prevCityValue : '';
                        return fetchSchools(selectedCountryId, citySelect.value);
                    })
                    .then(schools => {
                        schoolSelect.innerHTML = '<option value="">All Schools</option>';
                        (schools || []).forEach(school => {
                            const opt = document.createElement('option');
                            opt.value = school.id;
                            opt.textContent = school.name;
                            schoolSelect.appendChild(opt);
                        });
                        if (countryUpdateTimeout) { clearTimeout(countryUpdateTimeout); }
                        countryUpdateTimeout = setTimeout(() => {
                            if (seq === filtersEventSeq && citySelect.value === '' && schoolSelect.value === '') {
                                updateAirports(selectedCountryId, '', '');
                            }
                        }, 200);
                    })
                    .catch(err => console.error('Cities/schools fetch error:', err));
            });

            citySelect.addEventListener('change', function() {
                const selectedCityId = this.value;
                const selectedCountryId = countrySelect.value;
                filtersEventSeq++;
                if (countryUpdateTimeout) { clearTimeout(countryUpdateTimeout); }
                fetchSchools(selectedCountryId, selectedCityId)
                    .then(schools => {
                        schoolSelect.innerHTML = '<option value="">All Schools</option>';
                        (schools || []).forEach(school => {
                            const opt = document.createElement('option');
                            opt.value = school.id;
                            opt.textContent = school.name;
                            schoolSelect.appendChild(opt);
                        });
                        updateAirports(selectedCountryId, selectedCityId, schoolSelect.value);
                    })
                    .catch(err => console.error('Schools fetch error:', err));
            });

            schoolSelect.addEventListener('change', function() {
                const selectedSchoolId = this.value;
                updateAirports(countrySelect.value, citySelect.value, selectedSchoolId);
            });

            initSortable();
            bindPaginationLinks('', '', '');

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
                            return fetchSchools('', '');
                        })
                        .then(schools => {
                            schoolSelect.innerHTML = '<option value="">All Schools</option>';
                            (schools || []).forEach(school => {
                                const opt = document.createElement('option');
                                opt.value = school.id;
                                opt.textContent = school.name;
                                schoolSelect.appendChild(opt);
                            });
                            schoolSelect.value = '';
                        });
                    updateAirports('', '', '');
                }
            });
            citySelect.addEventListener('blur', function() {
                if (this.value === '') {
                    fetchSchools(countrySelect.value, '')
                        .then(schools => {
                            schoolSelect.innerHTML = '<option value="">All Schools</option>';
                            (schools || []).forEach(school => {
                                const opt = document.createElement('option');
                                opt.value = school.id;
                                opt.textContent = school.name;
                                schoolSelect.appendChild(opt);
                            });
                            schoolSelect.value = '';
                        });
                    updateAirports(countrySelect.value, '', '');
                }
            });
            schoolSelect.addEventListener('blur', function() {
                if (this.value === '') {
                    updateAirports(countrySelect.value, citySelect.value, '');
                }
            });
        });
    </script>
</x-app-layout>
