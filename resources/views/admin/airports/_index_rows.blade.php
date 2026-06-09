@forelse ($airports as $airport)
    <tr data-id="{{ $airport->id }}" class="sortable-row">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            <div class="flex items-center">
                <span class="drag-handle cursor-move mr-2">⋮⋮</span>
                {{ $airport->order }}
            </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $airport->name }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $airport->school->name ?? 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $airport->arrival_price ? number_format($airport->arrival_price, 2) : 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $airport->departure_price ? number_format($airport->departure_price, 2) : 'N/A' }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm">
            @if($airport->active)
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
            @else
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Inactive</span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <a href="{{ route('admin.airports.edit', $airport) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
            <form action="{{ route('admin.airports.destroy', $airport) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this airport?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No airports found.</td>
    </tr>
@endforelse