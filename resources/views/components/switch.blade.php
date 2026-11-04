@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'size' => 'default',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<input {{ $attributes->merge(['type' => 'checkbox', 'role' => 'switch', 'data-slot' => 'switch', 'data-size' => $size, ...$state->attributes()]) }}>
<x-ui::field.error :state="$state" />
