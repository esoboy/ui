@blaze
@props(['colspan' => 1])
<x-ui::table.row {{ $attributes->merge(['data-slot' => 'table-empty']) }}>
    <x-ui::table.cell :colspan="$colspan">{{ $slot }}</x-ui::table.cell>
</x-ui::table.row>
