<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Cities') }}
            </h2>
            <a href="{{ route('admin.cities.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Add New City') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- TODO: Add Success/Error Messages --}}

                    <!-- Country Filter -->
                    <div class="mb-4 flex items-center gap-3">
                        <label for="countryFilter" class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by Country:</label>
                        <select id="countryFilter" class="border-gray-300 dark:border-gray-700 rounded-md shadow-sm">
                            <option value="">All Countries</option>
                            @foreach($countries as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <button id="clearCountryFilter" class="px-3 py-2 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">Clear</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">City Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Country</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Active</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortable-cities" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @include('admin.cities._index_rows', ['cities' => $cities])
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4" id="cities-pagination">
                        @include('admin.cities._pagination', ['cities' => $cities])
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- SortableJS is bundled via Vite in resources/js/app.js --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortableElement = document.getElementById('sortable-cities');
            const countryFilter = document.getElementById('countryFilter');
            const clearFilterBtn = document.getElementById('clearCountryFilter');

            function initSortable() {
                if (sortableElement._sortableInstance) return; // prevent duplicate init
                const sortable = Sortable.create(sortableElement, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function(evt) {
                        const rows = sortableElement.querySelectorAll('.sortable-row');
                        const cities = [];
                        
                        rows.forEach((row, index) => {
                            cities.push({
                                id: row.dataset.id,
                                order: index
                            });
                        });
                        
                        // Send AJAX request to update order
                        fetch('{{ route("admin.cities.update-order") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ cities: cities })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Update order display
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
                sortableElement._sortableInstance = sortable;
            }

            function fetchFilteredCities(countryId, pageUrl = null) {
                const params = new URLSearchParams();
                if (countryId) params.append('country_id', countryId);
                // If a page URL is provided (from pagination), extract the page number
                if (pageUrl) {
                    try {
                        const urlObj = new URL(pageUrl, window.location.origin);
                        const page = urlObj.searchParams.get('page');
                        if (page) params.append('page', page);
                    } catch (e) { /* ignore */ }
                }

                fetch('{{ route("admin.cities.filter") }}' + '?' + params.toString(), {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                })
                .then(res => res.json())
                .then(data => {
                    if (!data || typeof data.rowsHtml !== 'string' || typeof data.paginationHtml !== 'string') {
                        console.error('Invalid filter response', data);
                        return;
                    }
                    // Replace rows
                    sortableElement.innerHTML = data.rowsHtml;
                    // Replace pagination
                    const pagination = document.getElementById('cities-pagination');
                    pagination.innerHTML = data.paginationHtml;
                    // Re-init sortable on new rows
                    sortableElement._sortableInstance = null;
                    initSortable();
                    // Re-bind pagination link interception after DOM update
                    bindPaginationLinks();
                })
                .catch(err => {
                    console.error('Filter error:', err);
                });
            }

            function bindPaginationLinks() {
                const pagination = document.getElementById('cities-pagination');
                if (!pagination) return;
                const links = pagination.querySelectorAll('a');
                links.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const currentCountryId = countryFilter ? countryFilter.value : '';
                        fetchFilteredCities(currentCountryId, this.href);
                    });
                });
            }

            // Initialize
            initSortable();

            // Filter events
            if (countryFilter) {
                countryFilter.addEventListener('change', function() {
                    fetchFilteredCities(this.value);
                });
            }
            if (clearFilterBtn) {
                clearFilterBtn.addEventListener('click', function() {
                    countryFilter.value = '';
                    fetchFilteredCities('');
                });
            }

            // Intercept initial pagination links to load via AJAX
            bindPaginationLinks();
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
