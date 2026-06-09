@forelse ($courses as $course)
    <tr data-id="{{ $course->id }}" class="sortable-row cursor-move bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            <span class="drag-handle cursor-move mr-2">⋮⋮</span>
            <span>{{ $course->order }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $course->name }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            {{ $course->school->name ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            {{ $course->courseType->name ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            {{ optional($course->juniorSettings?->start_date)->format('Y-m-d') ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
            {{ optional($course->juniorSettings?->end_date)->format('Y-m-d') ?? '-' }}
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
            @if($course->active)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    {{ __('Active') }}
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    {{ __('Inactive') }}
                </span>
            @endif
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <a href="{{ route('admin.junior-courses.edit', $course->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-200 mr-3">
                {{ __('Edit') }}
            </a>
            <form action="{{ route('admin.junior-courses.destroy', $course->id) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ __('Are you sure you want to delete this junior course?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-200">
                    {{ __('Delete') }}
                </button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300 text-center">
            {{ __('No junior courses found.') }}
        </td>
    </tr>
@endforelse
