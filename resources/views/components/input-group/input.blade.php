@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\PendingControl')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'type' => 'text',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);

PendingControl::put($state);
@endphp

<input {{ $attributes->merge(['type' => $type, 'data-slot' => 'input-group-control', ...$state->attributes()]) }}>
