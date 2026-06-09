@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-6">Agent Settings</h2>

                @if(session('success'))
                    <div class="mb-4 p-3 rounded bg-green-100 text-green-700">{{ session('success') }}</div>
                @endif

                <form action="{{ route('agent.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="brand_display_name">Display Name</label>
                            <input type="text" name="brand_display_name" id="brand_display_name" value="{{ old('brand_display_name', $settings->brand_display_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Your agency name">
                            @error('brand_display_name')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_email">Contact Email</label>
                            <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings->contact_email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="agent@example.com">
                            @error('contact_email')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="contact_phone">Contact Phone</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="+44 20 1234 5678">
                            @error('contact_phone')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text sm font-medium text-gray-700 mb-1" for="brand_logo">Brand Logo (JPG/PNG, max 1MB)</label>
                            <input type="file" name="brand_logo" id="brand_logo" class="mt-1 block w-full text-sm" accept="image/jpeg,image/png">
                            <p id="brandLogoError" class="text-sm text-red-600 mt-1 hidden"></p>
                            @error('brand_logo')
                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                            @enderror

                            <div class="mt-3" id="newLogoPreviewContainer" style="display:none;">
                                <p class="text-sm text-gray-700 mb-1">New Logo Preview:</p>
                                <img id="brandLogoPreview" alt="New Agent Brand Logo Preview" class="h-16 object-contain border rounded">
                            </div>

                            @if($settings->brand_logo_path)
                                <div class="mt-3">
                                    <p class="text-sm text-gray-700 mb-2">Current Logo:</p>
                                    <img src="{{ $settings->brandLogoUrl() }}" alt="Agent Brand Logo" class="h-16 object-contain border rounded" onerror="this.style.display='none'">
                                    <button type="submit" form="removeLogoForm" class="mt-2 px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">Remove Logo</button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="px-4 py-2 bg-bayswater-blue text-white rounded hover:bg-blue-700">Save Changes</button>
                    </div>
                </form>

                @if($settings->brand_logo_path)
                <form id="removeLogoForm" action="{{ route('agent.settings.logo.remove') }}" method="POST" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
                @endif

                <div class="border-t mt-8 pt-6">
                    <h3 class="text-lg font-semibold text-bayswater-blue mb-2">Notes</h3>
                    <ul class="list-disc pl-5 text-sm text-gray-600 space-y-1">
                        <li>Your logo appears alongside the Bayswater logo where branding is supported.</li>
                        <li>Contact details may be used on quotations and agent-facing pages.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('brand_logo');
        const previewContainer = document.getElementById('newLogoPreviewContainer');
        const img = document.getElementById('brandLogoPreview');
        const errorEl = document.getElementById('brandLogoError');

        if (input && previewContainer && img) {
            input.addEventListener('change', function() {
                const file = this.files && this.files[0];
                
                // Reset errors
                if (errorEl) {
                    errorEl.textContent = '';
                    errorEl.classList.add('hidden');
                }

                if (!file) {
                    // Input cleared
                    previewContainer.style.display = 'none';
                    img.src = '';
                    return;
                }

                // Validate type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!validTypes.includes(file.type) && !/\.(jpe?g|png)$/i.test(file.name)) {
                    if (errorEl) {
                        errorEl.textContent = 'Only JPG or PNG images are allowed.';
                        errorEl.classList.remove('hidden');
                    }
                    // Reset input and preview
                    this.value = '';
                    previewContainer.style.display = 'none';
                    img.src = '';
                    return;
                }

                // Validate size (1MB)
                if (file.size > 1024 * 1024) {
                    if (errorEl) {
                        errorEl.textContent = 'File size too large. Max 1MB.';
                        errorEl.classList.remove('hidden');
                    }
                    this.value = '';
                    previewContainer.style.display = 'none';
                    img.src = '';
                    return;
                }

                // Preview using FileReader
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    previewContainer.style.display = 'block';
                    img.style.display = 'block'; // Ensure image is visible
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
@endpush
