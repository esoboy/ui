@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'value' => null,
    'options' => null,
    'placeholder' => null,
    'disabled' => false,
    'size' => 'default',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('select'.(Model::key($attributes) ?? ''), $loop ?? null);
$settings = [
    'disabled' => $disabled,
    'placeholder' => $placeholder,
    'size' => $size,
    'number' => collect($attributes->whereStartsWith('wire:model')->getAttributes())->keys()->contains(fn ($name) => in_array('number', explode('.', $name), true)),
    'validation' => $state->attributes(),
];
@endphp

@livewire('ui.select', ['options' => $options, 'markup' => $options === null ? \Esoboy\Ui\Support\ViewSlots::capture($slot) : '', 'ownerId' => Model::component()?->getId(), 'settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], key('ui-select-'.$key))
<x-ui::field.error :state="$state" />
