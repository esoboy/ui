@blaze

@props([
    'variant' => 'ghost',
    'size' => 'icon-sm',
])

@php
$label = $attributes->get('aria-label');
$keys = $attributes->get('data-editor-keys');
$parts = $keys ? explode('-', $keys) : [];
$mac = ['Mod' => '⌘', 'Shift' => '⇧', 'Alt' => '⌥'];
$other = ['Mod' => 'Ctrl'];
@endphp

<x-ui::button :variant="$variant" :size="$size" :attributes="$attributes">{{ $slot }}@if ($label)
    <span popover="manual" role="tooltip" data-slot="tooltip-content" data-editor-tip data-side="top" data-align="center" style="--side-offset: 4px">
        <span>{{ $label }}</span>
        @if ($parts)
        <x-ui::kbd data-editor-tip-mac>{{ implode('', array_map(fn ($key) => $mac[$key] ?? strtoupper($key), $parts)) }}</x-ui::kbd>
        <x-ui::kbd data-editor-tip-other>{{ implode('+', array_map(fn ($key) => $other[$key] ?? ucfirst($key), $parts)) }}</x-ui::kbd>
        @endif
    </span>
@endif</x-ui::button>
