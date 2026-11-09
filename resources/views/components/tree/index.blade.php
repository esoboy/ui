@blaze

@use('Esoboy\Ui\Support\Model')

{{-- items: [['value' => ..., 'label' => ..., 'children' => [...], 'disabled' => bool, 'lazy' => bool], ...]. --}}
@props([
    'nodeView' => null,
    'items' => null,
    'checkable' => false,
    'value' => null,
    'expanded' => [],
    'expandAll' => false,
    'searchable' => false,
    'placeholder' => null,
    'load' => null,
    'draggable' => false,
    'disabled' => false,
])

@php
$value = Model::value($attributes, $value);
$items ??= [];
$settings = compact('nodeView', 'checkable', 'expanded', 'expandAll', 'searchable', 'placeholder', 'load', 'draggable', 'disabled');
$forward = $attributes->except('wire:key')->getAttributes();
if (isset($forward['wire:move'])) $forward['wire:move'] = '$parent.'.$forward['wire:move'];
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('tree'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

{!! \Esoboy\Ui\Support\ViewSlots::mount('ui.tree', ['items' => $items, 'settings' => $settings, 'value' => $value, ...$forward], 'ui-tree-'.$key, ['default' => $slot]) !!}
<x-ui::extras />
