@blaze(fold: true)

@props([
    'variant' => 'default',
    'inset' => false,
])

@php
// Choosing an item closes the whole menu, submenus included (ui.js).
$attributes = $attributes->merge([
    'role' => 'menuitem',
    'data-slot' => 'dropdown-menu-item',
    'data-variant' => $variant,
    'data-inset' => $inset,
]);
@endphp

@if ($attributes->has('href'))
<a {{ $attributes }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => 'button']) }}>{{ $slot }}</button>
@endif
