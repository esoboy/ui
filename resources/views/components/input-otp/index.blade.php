@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'maxlength' => 6,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

{{-- One real input laid over the slots; ui.js mirrors its characters into them. class styles the container. --}}
<eso-input-otp {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'input-otp']) }}>
    {{ $slot }}
    <input {{ $attributes->except(['class', 'style'])->merge([
        'type' => 'text',
        'data-slot' => 'input-otp-input',
        'maxlength' => $maxlength,
        'autocomplete' => 'one-time-code',
        'spellcheck' => 'false',
        ...$state->attributes(),
    ]) }}>
</eso-input-otp>
<x-ui::field.error :state="$state" />
