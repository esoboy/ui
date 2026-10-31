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
    <button {{ $attributes->only(['class', 'style']) }} type="button" data-slot="select-trigger" data-tree-select-trigger data-size="{{ $size }}" aria-haspopup="tree" aria-expanded="false" command="toggle-popover" commandfor="" {{ new \Illuminate\View\ComponentAttributeBag($state->attributes()) }} @if (! $labels) data-placeholder @endif @if ($disabled) disabled @endif>
        <span data-slot="select-value">{{ $labels ? implode(', ', $labels) : $placeholder }}</span>
        <x-ui::icon name="chevron-down" data-slot="select-icon" />
    </button>
    <div popover="auto" data-slot="tree-select-content" data-side="bottom" data-align="start" style="--side-offset: 4px;" wire:ignore.self>
        <x-ui::tree :items="$items" :checkable="$checkable" :default-value="$value" :default-expanded="$defaultExpanded" :expand-all="$expandAll" :searchable="$searchable" :load="$load" />
    </div>
</eso-tree-select>
<x-ui::field.error :state="$state" />
<x-ui::extras />
