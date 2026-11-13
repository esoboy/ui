@blaze

@props([
    'for',
    'colspan' => 1,
])

{{-- The expanded part of the row with that id: a full-width cell under it, hidden until its Row Toggle opens it. --}}
<tr data-slot="data-table-row-detail" data-detail-for="{{ $for }}" wire:key="data-table-row-detail-{{ $for }}" hidden>
    <td {{ $attributes->merge(['colspan' => $colspan, 'data-slot' => 'table-cell']) }}>{{ $slot }}</td>
</tr>
