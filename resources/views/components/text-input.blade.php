@props(['disabled' => false])

{{-- Remove dark mode classes to ensure contrast on light backgrounds --}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm']) }}>
