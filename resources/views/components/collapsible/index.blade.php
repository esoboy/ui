@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'defaultOpen' => false,
])

@php
$open = (bool) ($defaultOpen ?: Model::value($attributes));
@endphp

{{-- <eso-collapsible> exposes the open state as .value, so wire:model binds on it. --}}
<eso-collapsible {{ $attributes->merge(['data-slot' => 'collapsible', 'data-open' => $open]) }}>{{ $slot }}</eso-collapsible>
