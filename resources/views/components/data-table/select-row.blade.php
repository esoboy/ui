@blaze

@props([
    'value',
])

@php
// Bound to WithDataTable's selected rows unless given its own wire:model.
$bound = collect($attributes->getAttributes())->keys()->contains(fn ($name) => str_starts_with($name, 'wire:model'));
@endphp

<x-ui::checkbox :attributes="$attributes->merge([...($bound ? [] : ['wire:model.live' => 'selected']), 'value' => $value, 'data-data-table-select-row' => true, 'aria-label' => __('ui::data-table.select_row')])" />
