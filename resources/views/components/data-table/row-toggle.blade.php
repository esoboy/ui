@blaze

@props(['kind'])

<x-ui::button variant="ghost" size="icon-xs" :attributes="$attributes->merge(['data-data-table-row-toggle' => $kind, 'aria-expanded' => 'false', 'aria-label' => $kind === 'tree' ? __('ui::data-table.expand_tree') : __('ui::data-table.expand_detail')])"><x-ui::icon name="chevron-right" /></x-ui::button>
