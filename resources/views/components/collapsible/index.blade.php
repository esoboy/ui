@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'open' => false,
    'uiModel' => null,
    'uiOpen' => null,
])

@php
$open = (bool) Model::value($attributes, $open);
@endphp

<eso-collapsible {{ $attributes->whereDoesntStartWith('wire:model')->merge(['data-slot' => 'collapsible', 'data-open' => $open, 'x-data' => 'uiCollapsible('.json_encode(Model::binding($attributes)).','.json_encode($open).')', 'x-effect' => 'refresh()', 'x-on:click' => 'toggle($event)', 'x-bind:data-open' => 'open']) }}>{{ $slot }}</eso-collapsible>
