@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- An Input Group with − and + buttons; arrows step too, and holding a button repeats. --}}
@props([
    'min' => null,
    'max' => null,
    'step' => 1,
    'placeholder' => null,
    'disabled' => false,
    'value' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$value = is_numeric($value) ? $value + 0 : null;
$settings = compact('min', 'max', 'step', 'placeholder', 'disabled');
$settings['validation'] = $state->attributes();
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('number-input'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

@livewire('ui.number-input', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], key('ui-number-input-'.$key))
<x-ui::field.error :state="$state" />
<x-ui::extras />
