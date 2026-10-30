@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'defaultValue' => null,
    'orientation' => 'horizontal',
])

@php
$value = $defaultValue ?? Model::value($attributes);
@endphp

{{-- <eso-tabs> exposes the active tab as .value, so wire:model binds on the tabs themselves. --}}
<eso-tabs {{ $attributes->merge(['data-slot' => 'tabs', 'data-orientation' => $orientation, 'data-value' => is_scalar($value) ? (string) $value : false]) }}>{{ $slot }}</eso-tabs>
