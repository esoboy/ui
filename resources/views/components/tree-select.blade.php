@blaze

@use('Esoboy\Ui\Support\Options')
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- A Select-style trigger opening a Tree; checkable picks several, searchable puts a search box at the top, load fills lazy items. --}}
@props([
    'items' => [],
    'checkable' => false,
    'placeholder' => null,
    'size' => 'default',
    'disabled' => false,
    'defaultValue' => null,
    'defaultExpanded' => [],
    'expandAll' => false,
    'searchable' => false,
    'load' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = $defaultValue ?? Model::value($attributes);
$labels = Options::find($items, $checkable ? (array) ($value ?? []) : ($value === null ? [] : [$value]));
@endphp

{{-- <eso-tree-select> proxies its tree's .value, so wire:model binds on it. --}}
<eso-tree-select {{ $attributes->except(['name', 'class', 'style'])->merge(['data-slot' => 'tree-select', 'data-placeholder' => $placeholder ?? false, 'data-checkable' => (bool) $checkable]) }}>
    <x-ui::select.trigger :size="$size" data-tree-select-trigger aria-haspopup="tree" :data-placeholder="! $labels" :disabled="$disabled" :attributes="$attributes->only(['class', 'style'])->merge($state->attributes())">
        <x-ui::select.value :placeholder="$labels ? implode(', ', $labels) : $placeholder" />
    </x-ui::select.trigger>
    <div popover="auto" data-slot="tree-select-content" data-side="bottom" data-align="start" style="--side-offset: 4px;" wire:ignore.self>
        <x-ui::tree :items="$items" :checkable="$checkable" :default-value="$value" :default-expanded="$defaultExpanded" :expand-all="$expandAll" :searchable="$searchable" :load="$load" />
    </div>
</eso-tree-select>
<x-ui::field.error :state="$state" />
<x-ui::extras />
