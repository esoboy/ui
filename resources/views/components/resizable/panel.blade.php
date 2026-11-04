@blaze

@props([
    'defaultSize' => null,
    'minSize' => null,
    'maxSize' => null,
])

@php
// Sizes are percentages of the group, like react-resizable-panels'.
$size = $defaultSize === null ? null : (float) $defaultSize;
@endphp

{{-- wire:ignore.self keeps morphs from resetting a size the user dragged. --}}
<div {{ $attributes->merge([
    'data-slot' => 'resizable-panel',
    'data-default-size' => $size ?? false,
    'data-min-size' => $minSize === null ? false : (float) $minSize,
    'data-max-size' => $maxSize === null ? false : (float) $maxSize,
    'style' => 'flex: '.($size ?? 1).' 1 0px; overflow: hidden;',
]) }} wire:ignore.self>{{ $slot }}</div>
