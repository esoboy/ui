@blaze(fold: true)

{{-- <eso-data-table> checks every row from the select-all box and marks selected rows. --}}
<eso-data-table {{ $attributes->merge(['data-slot' => 'data-table']) }}>{{ $slot }}</eso-data-table>
