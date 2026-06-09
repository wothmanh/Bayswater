@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-6">Market Discounts</h2>

                @if(session('success'))
                    <div class="mb-4 text-green-600">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-4 text-red-600">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
                    @csrf
                    @method('PUT')
                    {{-- Hidden field to tell Controller which section this is --}}
                    <input type="hidden" name="_settings_section" value="market-discount">

                    <div id="discounts-container">
                        @forelse($marketDiscounts as $index => $discount)
                            @include('admin.market-discount._form_item', ['index' => $index, 'discount' => $discount, 'regions' => $regions])
                        @empty
                            {{-- Show one empty item if none exist, or user can click Add --}}
                            {{-- Actually, let's just start with one empty if none exist, or rely on the Add button. 
                               User requirement says "Add a new tab" button. 
                               But if there are 0, we might want to show 0. 
                               However, the controller validation says 'discounts' is nullable array.
                            --}}
                        @endforelse
                    </div>

                    <div class="pt-4">
                        <button type="button" id="add-discount-btn" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            + Add a new tab
                        </button>
                    </div>

                    <div class="pt-6 border-t mt-6">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-bayswater-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Template for new discount item --}}
<template id="discount-template">
    <div class="discount-item border rounded-md p-6 mb-6 bg-gray-50 relative">
        <button type="button" class="remove-discount-btn absolute top-4 right-4 text-red-600 hover:text-red-800 font-semibold text-sm">
            Remove
        </button>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tab Title</label>
                <input type="text" name="discounts[INDEX][title]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Market Discount" required />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">iFrame Link</label>
                <input type="url" name="discounts[INDEX][iframe_url]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com/discounts" required />
            </div>
        </div>
        
        <div class="border-t pt-4">
            <h3 class="text-sm font-medium text-gray-900 mb-2">Visible Regions</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($regions as $region)
                    <div class="flex items-center">
                        <input type="checkbox" name="discounts[INDEX][region_ids][]" value="{{ $region->id }}" 
                            id="region_{{ $region->id }}_INDEX"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="region_{{ $region->id }}_INDEX" class="ml-2 block text-sm text-gray-700">
                            {{ $region->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('discounts-container');
        const addButton = document.getElementById('add-discount-btn');
        const template = document.getElementById('discount-template').innerHTML;
        
        // Use a counter that starts higher than any existing index to avoid collisions
        // PHP loop uses numeric keys 0, 1, 2...
        // We can just use Date.now() for unique indices in the frontend for new items
        
        addButton.addEventListener('click', function() {
            const index = 'new_' + Date.now(); // Unique index
            const html = template.replace(/INDEX/g, index);
            container.insertAdjacentHTML('beforeend', html);
        });
        
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-discount-btn')) {
                if (confirm('Are you sure you want to remove this tab?')) {
                    e.target.closest('.discount-item').remove();
                }
            }
        });
    });
</script>
@endsection
