@blaze(fold: true, unsafe: ['href', 'type'])

@props([
    'variant' => 'default',
])

@php
$attributes = $attributes->merge(['data-slot' => 'marker', 'data-variant' => $variant]);
@endphp

{{-- A link with href, a button with type (e.g. "Load earlier"), otherwise a div. --}}
@if ($attributes->has('href'))
<a {{ $attributes }}>{{ $slot }}</a>
@elseif ($attributes->has('type'))
<button {{ $attributes }}>{{ $slot }}</button>
@else
<div {{ $attributes }}>{{ $slot }}</div>
@endif
