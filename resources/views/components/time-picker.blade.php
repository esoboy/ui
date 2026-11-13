@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')
@use('Esoboy\Ui\Support\ViewSlots')

@aware(['uiField' => false, 'showError' => true])

@props([
    'value' => null,
    'placeholder' => null,
    'mode' => 'segmented',
    'step' => null,
    'min' => null,
    'max' => null,
    'disabled' => false,
    'readonly' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$settings = compact('placeholder', 'mode', 'step', 'min', 'max', 'disabled', 'readonly');
$settings['validation'] = $state->attributes();
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('time-picker'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

{!! ViewSlots::mount('ui.time-picker', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], 'ui-time-picker-'.$key, ['default' => $slot]) !!}
<x-ui::field.error :state="$state" />
