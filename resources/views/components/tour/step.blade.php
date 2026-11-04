@blaze

{{-- One step: target is a CSS selector (without one, or when nothing matches, the panel stands in the middle); side and align place the panel as Popover's do. --}}
@props([
    'target' => null,
    'title' => null,
    'side' => 'bottom',
    'align' => 'center',
])

<template {{ $attributes->merge(['data-tour-step' => true, 'data-target' => $target ?? false, 'data-side' => $side, 'data-align' => $align]) }}>
    @if ($title)
    <h2 data-slot="tour-title">{{ $title }}</h2>
    @endif
    <div data-slot="tour-description">{{ $slot }}</div>
</template>
