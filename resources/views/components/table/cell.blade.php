@blaze(fold: true)

@props([
    'sticky' => null,
])

{{-- sticky="start" or "end" fixes the column to that edge as the table scrolls sideways. --}}
<td {{ $attributes->merge(['data-slot' => 'table-cell', 'data-sticky' => $sticky]) }}>{{ $slot }}</td>
