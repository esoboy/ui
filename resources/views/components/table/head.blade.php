@blaze(fold: true)

@props([
    'sticky' => null,
])

{{-- sticky="start" or "end" fixes the column to that edge as the table scrolls sideways. --}}
<th {{ $attributes->merge(['data-slot' => 'table-head', 'data-sticky' => $sticky]) }}>{{ $slot }}</th>
