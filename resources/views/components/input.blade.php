@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'type' => 'text',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<input {{ $attributes->merge(['type' => $type, 'data-slot' => 'input', ...$state->attributes()]) }}>
<x-ui::field.error :state="$state" />
