{{-- Use Bayswater blue-light for secondary buttons --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-bayswater-blue-light border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bayswater-blue focus:bg-bayswater-blue active:bg-bayswater-blue focus:outline-none focus:ring-2 focus:ring-bayswater-blue-light focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
