<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Exchange Names') }}
            </h2>
            <a href="{{ route('admin.exchange-names.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                {{ __('Add New Exchange Name') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Success message --}}
                    @if (session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Code (ISO 4217)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Display Label (Optional)</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="sortable-exchange-names" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($exchangeNames as $exchangeName)
                                    <tr data-id="{{ $exchangeName->id }}" class="sortable-row cursor-move">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                            <span class="drag-handle">⋮⋮</span>
                                            <span class="ml-2">{{ $exchangeName->order }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $exchangeName->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $exchangeName->label ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.exchange-names.edit', $exchangeName) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-3">Edit</a>
                                            <form action="{{ route('admin.exchange-names.destroy', $exchangeName) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this exchange name?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">No exchange names found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Links --}}
                    <div class="mt-4">
                        {{ $exchangeNames->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- SortableJS is bundled via Vite in resources/js/app.js --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sortableElement = document.getElementById('sortable-exchange-names');
            if (!sortableElement) return;
            const sortable = Sortable.create(sortableElement, {
                handle: '.drag-handle',
                animation: 150,
                onEnd: function(evt) {
                    const rows = sortableElement.querySelectorAll('.sortable-row');
                    const exchange_names = [];

                    rows.forEach((row, index) => {
                        exchange_names.push({
                            id: row.dataset.id,
                            order: index
                        });
                    });

                    fetch('{{ route("admin.exchange-names.update-order") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ exchange_names })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' || data.success) {
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