@blaze

@props([
    'colspan' => 1,
])

<x-ui::table.row>
    <x-ui::table.cell :colspan="$colspan" :attributes="$attributes->merge(['data-data-table-empty' => true])">{{ __('ui::data-table.no_results') }}</x-ui::table.cell>
</x-ui::table.row>
