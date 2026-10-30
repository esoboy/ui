@blaze

{{-- Checks or clears every row checkbox of the table; mixed while only some are checked. --}}
<x-ui::checkbox :attributes="$attributes->merge(['data-data-table-select-all' => true, 'aria-label' => __('ui::data-table.select_all')])" />
