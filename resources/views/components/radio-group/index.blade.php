@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'defaultValue' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

{{-- <eso-radio-group> exposes the checked radio as .value, so wire:model binds on the group itself. --}}
<eso-radio-group {{ $attributes->merge(['role' => 'radiogroup', 'data-slot' => 'radio-group', ...$state->attributes()]) }}>{{ $slot }}</eso-radio-group>
<x-ui::field.error :state="$state" />
