@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<input {{ $attributes->merge(['type' => 'checkbox', 'data-slot' => 'checkbox', ...$state->attributes()]) }}>
{{-- A checkbox with its own value is one option of a set; the set's error belongs to <x-ui::field.error>. --}}
@unless ($attributes->has('value'))
<x-ui::field.error :state="$state" />
@endunless
