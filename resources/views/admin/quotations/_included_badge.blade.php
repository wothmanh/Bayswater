@props(['item', 'context' => 'web'])

@if($item['is_included'] ?? false)
    {{ str_replace(' (Included)', '', $item['name']) }}
    @if($context === 'pdf')
        <span style="color:#16a34a; background-color:#f0fdf4; border-radius:4px; padding:2px 6px; font-size:10px; font-weight:500; margin-left:4px;">Included</span>
    @else
        <span class="bg-green-50 text-green-600 rounded px-2 py-0.5 text-xs font-medium ml-1">Included</span>
    @endif
@else
    {{ $item['name'] }}
@endif
