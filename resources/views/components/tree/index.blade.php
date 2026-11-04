@blaze

@use('Esoboy\Ui\Support\Model')

{{-- items: [['value' => ..., 'label' => ..., 'children' => [...], 'disabled' => bool, 'lazy' => bool], ...], or nested <x-ui::tree.item> tags. --}}
@props([
    'items' => null,
    'checkable' => false,
    'defaultValue' => null,
    'defaultExpanded' => [],
    'expandAll' => false,
    'searchable' => false,
    'placeholder' => null,
    'load' => null,
    'draggable' => false,
])

@php
$value = $defaultValue ?? Model::value($attributes);
$value = $checkable ? array_values(array_map('strval', (array) ($value ?? []))) : ($value === null || $value === '' ? null : (string) $value);
@endphp

{{--
    <eso-tree> exposes the chosen item (or the checked items, when checkable) as .value, so wire:model binds on it.
    searchable filters it by label; load names the Livewire method that adds a lazy item's children; draggable dispatches
    move ({ value, target, position: before | after | inside }) for wire:move. aria-* attributes go to the tree itself.
--}}
<eso-tree {{ $attributes->whereDoesntStartWith('aria-')->merge([
    'data-slot' => 'tree',
    'data-checkable' => (bool) $checkable,
    'data-value' => json_encode($value),
    'data-load' => $load,
    'data-draggable' => (bool) $draggable,
]) }}>
    @if ($searchable)
    <div data-slot="tree-search">
        <x-ui::input-group>
            <x-ui::input-group.input data-tree-search :placeholder="$placeholder ?? __('ui::tree.search')" :aria-label="__('ui::tree.search')" autocomplete="off" spellcheck="false" class="px-2 text-sm" />
            <x-ui::input-group.addon><x-ui::icon name="search" /></x-ui::input-group.addon>
        </x-ui::input-group>
    </div>
    @endif
    <ul {{ $attributes->whereStartsWith('aria-')->merge(['role' => 'tree', 'data-slot' => 'tree-list', 'aria-multiselectable' => $checkable ? 'true' : false]) }}>
        @if ($items !== null)
        @foreach ($items as $item)
        <x-ui::tree.item :item="$item" />
        @endforeach
        @else
        {{ $slot }}
        @endif
    </ul>
    @if ($searchable)
    <div data-slot="tree-empty" hidden>{{ __('ui::tree.no_results') }}</div>
    @endif
</eso-tree>
<x-ui::extras />
