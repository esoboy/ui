@blaze

@props([
    'isActive' => false,
    'variant' => 'default',
    'size' => 'default',
    'tooltip' => null,
])

@php
// The tooltip only shows while the sidebar is collapsed to icons (sidebar.css hides it otherwise).
$attributes = $attributes->merge([
    'data-slot' => 'sidebar-menu-button',
    'data-sidebar' => 'menu-button',
    'data-variant' => $variant,
    'data-size' => $size,
    'data-active' => $isActive,
    'data-tooltip-trigger' => $tooltip !== null,
]);
@endphp

@if ($attributes->has('href'))
<a {{ $attributes }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => 'button']) }}>{{ $slot }}</button>
@endif
@if ($tooltip !== null)
<x-ui::tooltip.content side="right" align="center" data-sidebar="menu-tooltip">{{ $tooltip }}</x-ui::tooltip.content>
@endif
