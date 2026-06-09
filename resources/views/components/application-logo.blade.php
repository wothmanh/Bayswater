@php
    $settings = \App\Models\Setting::first();
@endphp

@if($settings && $settings->logo_path)
    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Bayswater Logo" {{ $attributes }} />
@else
    <img src="https://d2zqc3k48kil1s.cloudfront.net/_nuxt/logo-white.6f274f1c.png" alt="Bayswater Logo" {{ $attributes }} />
@endif
