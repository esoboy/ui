@blaze(fold: true, unsafe: ['href'])

@props([
    'variant' => 'default',
    'size' => 'default',
])

@php
$attributes = $attributes->merge(['data-slot' => 'item', 'data-variant' => $variant, 'data-size' => $size]);
@endphp

@if ($attributes->has('href'))
<a {{ $attributes }}>{{ $slot }}</a>
@else
<div {{ $attributes }}>{{ $slot }}</div>
@endif
