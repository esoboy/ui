@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'maxlength' => 6,
    'value' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value) ?? '';
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('input-otp'.(Model::key($attributes) ?? ''), $loop ?? null);
$settings = ['maxlength' => max(1, (int) $maxlength), 'validation' => $state->attributes()];
@endphp

@livewire('ui.input-otp', ['value' => $value, 'layout' => (string) $slot, 'settings' => $settings, ...$attributes->except('wire:key')->getAttributes()], key('ui-input-otp-'.$key))
<x-ui::field.error :state="$state" />
