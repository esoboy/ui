@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Not in shadcn: a Popover with a saturation/brightness area, a hue slider, a hex Input and swatches. --}}
@props([
    'placeholder' => null,
    'value' => null,
    'swatches' => [],
    'disabled' => false,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$settings = compact('placeholder', 'disabled', 'swatches');
$settings['validation'] = $state->attributes();
$key = $attributes->get('wire:key') ?? Model::scope('color-picker', $loop ?? null);
@endphp

@livewire('ui.color-picker', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], key($key))
<x-ui::field.error :state="$state" />
<x-ui::extras />
