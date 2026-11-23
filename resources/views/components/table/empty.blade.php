@blaze
@props(['colspan' => 1])
<x-ui::table.row {{ $attributes->class('ui-table-empty') }}>
    <x-ui::table.cell :colspan="$colspan">{{ $slot }}</x-ui::table.cell>
</x-ui::table.row>
