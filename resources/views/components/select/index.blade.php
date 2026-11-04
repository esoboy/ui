@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'defaultValue' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = $defaultValue ?? Model::value($attributes);
@endphp

{{-- <eso-select> exposes the chosen item as .value, so wire:model binds on the select itself. --}}
<eso-select {{ $attributes->except('name')->merge(['data-slot' => 'select', 'data-value' => is_scalar($value) ? (string) $value : false, 'data-name' => $attributes->get('name') ?? false, ...$state->attributes()]) }}>
    {{ $slot }}
    @if ($attributes->has('name'))
        <input type="hidden" name="{{ $attributes->get('name') }}" value="{{ is_scalar($value) ? $value : '' }}">
    @endif
</eso-select>
<x-ui::field.error :state="$state" />
