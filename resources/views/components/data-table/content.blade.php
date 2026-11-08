@blaze

@props([
    'stickyHeader' => false,
])

{{-- The bordered frame around the table; put the header and body inside. With sticky-header and a height, the table scrolls inside it under its header (and above its footer). --}}
<div {{ $attributes->merge(['data-slot' => 'data-table-content', 'data-sticky-header' => (bool) $stickyHeader]) }}>
    <x-ui::table>{{ $slot }}</x-ui::table>
</div>
