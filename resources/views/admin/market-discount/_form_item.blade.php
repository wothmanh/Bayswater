<div class="discount-item border rounded-md p-6 mb-6 bg-white shadow-sm">
    <input type="hidden" name="discounts[{{ $index }}][id]" value="{{ $discount->id }}">
    
    <div class="flex justify-between items-center mb-4 pb-2 border-b border-gray-100">
        <h4 class="text-sm font-bold text-gray-700 uppercase tracking-wide">
            Discount Tab <span class="text-gray-400">#{{ $index + 1 }}</span>
        </h4>
        <button type="button" class="remove-discount-btn text-red-600 hover:text-red-800 font-semibold text-sm flex items-center transition-colors duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Remove
        </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Tab Title</label>
            <input type="text" name="discounts[{{ $index }}][title]" value="{{ old('discounts.'.$index.'.title', $discount->title) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Market Discount" required />
            @error('discounts.'.$index.'.title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">iFrame Link</label>
            <input type="url" name="discounts[{{ $index }}][iframe_url]" value="{{ old('discounts.'.$index.'.iframe_url', $discount->iframe_url) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="https://example.com/discounts" required />
            @error('discounts.'.$index.'.iframe_url')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
    
    <div class="border-t pt-4">
        <h3 class="text-sm font-medium text-gray-900 mb-2">Visible Regions</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($regions as $region)
                <div class="flex items-center">
                    <input type="checkbox" name="discounts[{{ $index }}][region_ids][]" value="{{ $region->id }}" 
                        id="region_{{ $region->id }}_{{ $index }}"
                        @checked(old('discounts.'.$index.'.region_ids') ? in_array($region->id, old('discounts.'.$index.'.region_ids')) : $discount->regions->contains($region->id)) 
                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="region_{{ $region->id }}_{{ $index }}" class="ml-2 block text-sm text-gray-700">
                        {{ $region->name }}
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>
