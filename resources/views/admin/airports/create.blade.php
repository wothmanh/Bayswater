<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Add New Airport') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.airports.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Name --}}
                            <div>
                                <x-input-label for="name" :value="__('Airport Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            {{-- School --}}
                            <div>
                                <x-input-label for="school_id" :value="__('Associated School')" />
                                <select name="school_id" id="school_id" class="block mt-1 w-full border-gray-300 focus:border-bayswater-blue focus:ring-bayswater-blue rounded-md shadow-sm" required>
                                    <option value="">-- Select School --</option>
                                    @foreach($schools as $id => $name)
                                        <option value="{{ $id }}" {{ old('school_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                            </div>

                            {{-- Arrival Price --}}
                            <div>
                                <x-input-label for="arrival_price" :value="__('Arrival Transfer Price')" />
                                <x-text-input id="arrival_price" class="block mt-1 w-full" type="number" step="0.01" name="arrival_price" :value="old('arrival_price')" />
                                <x-input-error :messages="$errors->get('arrival_price')" class="mt-2" />
                            </div>

                            {{-- Departure Price --}}
                            <div>
                                <x-input-label for="departure_price" :value="__('Departure Transfer Price')" />
                                <x-text-input id="departure_price" class="block mt-1 w-full" type="number" step="0.01" name="departure_price" :value="old('departure_price')" />
                                 <x-input-error :messages="$errors->get('departure_price')" class="mt-2" />
                             </div>

                             {{-- Active Status --}}
                             <div class="md:col-span-2">
                                 <label for="active" class="inline-flex items-center">
                                     <input id="active" type="checkbox" class="rounded border-gray-300 text-bayswater-blue shadow-sm focus:ring-bayswater-blue" name="active" value="1" checked>
                                     <span class="ms-2 text-sm text-gray-600">{{ __('Active') }}</span>
                                 </label>
                             </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.airports.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Save Airport') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
