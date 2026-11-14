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
    'nodeView' => null,
    'items' => [],
    'checkable' => false,
    'placeholder' => null,
    'size' => 'default',
    'disabled' => false,
    'value' => null,
    'expanded' => [],
    'expandAll' => false,
    'searchable' => false,
    'load' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$validation = $state->attributes();
$settings = compact('nodeView', 'checkable', 'placeholder', 'size', 'disabled', 'expanded', 'expandAll', 'searchable', 'load', 'validation');
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('tree-select'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

{!! \Esoboy\Ui\Support\ViewSlots::mount('ui.tree-select', ['items' => $items, 'settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], 'ui-tree-select-'.$key, ['default' => $slot]) !!}
<x-ui::field.error :state="$state" />
<x-ui::extras />
