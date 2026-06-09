{{-- Use Bayswater outline style for outline buttons --}}
<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-transparent border-2 border-bayswater-blue rounded-md font-semibold text-xs text-bayswater-blue uppercase tracking-widest hover:bg-bayswater-blue hover:text-white focus:bg-bayswater-blue focus:text-white active:bg-bayswater-blue active:text-white focus:outline-none focus:ring-2 focus:ring-bayswater-blue focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
