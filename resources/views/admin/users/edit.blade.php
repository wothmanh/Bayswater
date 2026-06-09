<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit User') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" role="alert">
                            <strong class="font-bold">Success!</strong>
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    {{-- Display Validation Errors --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="alert">
                            <strong class="font-bold">Validation Error!</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Main user update form --}}
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- User Details --}}
                        <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">User Details</h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                                <x-input-error :messages="$errors->get('name')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="username" :value="__('Username (Optional)')" />
                                <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username', $user->username)" />
                                <x-input-error :messages="$errors->get('username')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="role" :value="__('Role')" />
                                <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm" required>
                                    @foreach ($roles as $key => $label)
                                        <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>
                        </div>

                        @php $isAgent = old('role', $user->role) === 'agent'; @endphp
                        @if ($isAgent)
                        <div class="border-t pt-6 mt-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Agent Custom Branding & Contact Info</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-1">
                                    <x-input-label for="brand_display_name" :value="__('Display Name')" />
                                    <x-text-input id="brand_display_name" class="block mt-1 w-full" type="text" name="brand_display_name" :value="old('brand_display_name', optional($user->agentSetting)->brand_display_name)" />
                                    <x-input-error :messages="$errors->get('brand_display_name')" class="mt-2" />
                                </div>
                                <div class="md:col-span-1">
                                    <x-input-label for="contact_email" :value="__('Contact Email')" />
                                    <x-text-input id="contact_email" class="block mt-1 w-full" type="email" name="contact_email" :value="old('contact_email', optional($user->agentSetting)->contact_email)" />
                                    <x-input-error :messages="$errors->get('contact_email')" class="mt-2" />
                                </div>
                                <div class="md:col-span-1">
                                    <x-input-label for="contact_phone" :value="__('Contact Phone')" />
                                    <x-text-input id="contact_phone" class="block mt-1 w-full" type="text" name="contact_phone" :value="old('contact_phone', optional($user->agentSetting)->contact_phone)" />
                                    <x-input-error :messages="$errors->get('contact_phone')" class="mt-2" />
                                </div>
                                <div class="md:col-span-1">
                                    <x-input-label for="contact_whatsapp" :value="__('WhatsApp')" />
                                    <x-text-input id="contact_whatsapp" class="block mt-1 w-full" type="text" name="contact_whatsapp" :value="old('contact_whatsapp', optional($user->agentSetting)->contact_whatsapp)" />
                                    <x-input-error :messages="$errors->get('contact_whatsapp')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="brand_logo" :value="__('Agent Logo')" />
                                    <input id="brand_logo" name="brand_logo" type="file" accept="image/*" class="block mt-1 w-full text-sm" />
                                    @if(optional($user->agentSetting)->brand_logo_path)
                                        <div class="mt-3">
                                            <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">Current Logo:</p>
                                            <div class="flex items-center gap-3">
                                                <img src="{{ $user->agentSetting->brandLogoUrl() }}" alt="Agent Brand Logo" class="h-12 object-contain border rounded">
                                                <button type="submit" form="removeAgentLogoForm" class="px-3 py-1 border border-red-300 text-red-600 rounded text-sm hover:bg-red-50">
                                                    {{ __('Delete Logo') }}
                                                </button>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Upload a new image to replace the current logo.</p>
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">No logo uploaded.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="border-t pt-6 mt-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Region Assignment</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Select the regions this user is allowed to access.
                            </p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                @foreach($regions as $region)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="region_{{ $region->id }}" name="regions[]" type="checkbox" value="{{ $region->id }}"
                                                @checked($user->regions->contains($region->id))
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="region_{{ $region->id }}" class="font-medium text-gray-700 dark:text-gray-300">{{ $region->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="border-t pt-6 mt-8">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">Password</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="password" :value="__('New Password (Optional)')" />
                                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden, separate DELETE form to safely remove only the logo --}}
    @if(optional($user->agentSetting)->brand_logo_path)
        <form id="removeAgentLogoForm" method="POST" action="{{ route('admin.users.delete-logo', $user) }}" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif

    {{-- Optional JS to remove preview dynamically on success via flash message --}}
    @if(session('success'))
        <script>
            (function(){
                if ({{ json_encode(session('success')) }} === 'Logo removed successfully.') {
                    const previewContainer = document.querySelector('div.mt-3');
                    if (previewContainer) {
                        previewContainer.remove();
                        const noLogo = document.createElement('p');
                        noLogo.className = 'text-sm text-gray-500 dark:text-gray-400 mt-2';
                        noLogo.textContent = 'No logo uploaded.';
                        const logoCol = document.querySelector('#brand_logo')?.closest('.md\\:col-span-2');
                        if (logoCol) logoCol.appendChild(noLogo);
                    }
                }
            })();
        </script>
    @endif
</x-app-layout>
