@blaze

{{-- Expands or collapses its row's detail or children; on a row with neither it keeps its space, hidden. --}}
<x-ui::button variant="ghost" size="icon-xs" :attributes="$attributes->merge(['data-data-table-row-toggle' => true, 'aria-expanded' => 'false', 'aria-label' => __('ui::data-table.expand_row')])"><x-ui::icon name="chevron-right" /></x-ui::button>
