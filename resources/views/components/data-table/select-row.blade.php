@blaze

@aware(['selection' => 'selected'])

@props([
    'value',
])

<x-ui::checkbox :attributes="$attributes->whereDoesntStartWith('wire:model')->merge(['wire:model.live' => $selection, 'value' => $value, 'data-data-table-select-row' => true, 'aria-label' => __('ui::data-table.select_row')])" />
