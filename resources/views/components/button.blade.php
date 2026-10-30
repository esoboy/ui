@blaze(fold: true, unsafe: ['href'])

@props([
    'variant' => 'default',
    'size' => 'default',
    'type' => 'button',
])

@php
$attributes = $attributes->merge([
    'data-slot' => 'button',
    'data-variant' => $variant,
    'data-size' => $size,
]);
@endphp

@if ($attributes->has('href'))
<a {{ $attributes }}>{{ $slot }}</a>
@else
<button {{ $attributes->merge(['type' => $type]) }}>{{ $slot }}</button>
@endif
