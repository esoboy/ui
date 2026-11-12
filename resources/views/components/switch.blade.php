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
$attributes = \Esoboy\Ui\Support\Theme::control($attributes);
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<input {{ $attributes->merge(['type' => 'checkbox', 'checked' => \Esoboy\Ui\Support\Model::checked($attributes, (bool) $attributes->get('checked', false), $attributes->get('value')), 'role' => 'switch', 'data-slot' => 'switch', 'data-size' => $size, ...$state->attributes()]) }}>
<x-ui::field.error :state="$state" />
