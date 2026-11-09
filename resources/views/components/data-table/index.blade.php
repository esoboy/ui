@blaze(fold: true)

@props([
    'selectable' => false,
    'selection' => 'selected',
])

{{-- <eso-data-table> checks every row from the select-all box and marks selected rows. --}}
<eso-data-table {{ $attributes->merge([
    'data-slot' => 'data-table',
    'data-selection' => $selection,
    'x-data' => 'uiDataTable',
    'x-effect' => $selectable ? '$wire[$el.dataset.selection]; $nextTick(() => _sync())' : null,
]) }}>{{ $slot }}</eso-data-table>
