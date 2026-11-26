@props(['target', 'index'])
@aware(['tour' => null])
@php($tour?->register((int) $index))
<section {{ $attributes->merge(['class' => 'ui-tour-step', 'data-target' => $target, 'data-index' => $index]) }}
    wire:show="index === @js((int) $index)">{{ $slot }}</section>
