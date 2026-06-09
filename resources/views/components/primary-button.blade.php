{{-- Use Bayswater orange for primary buttons --}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-bayswater-orange border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-bayswater-orange-dark focus:bg-bayswater-orange-dark active:bg-bayswater-orange-dark focus:outline-none focus:ring-2 focus:ring-bayswater-orange focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
