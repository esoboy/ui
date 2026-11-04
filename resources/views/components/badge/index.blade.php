@blaze(fold: true, unsafe: ['href'])

@props([
    'variant' => 'default',
    'count' => null,
    'max' => 99,
    'showZero' => false,
    'dot' => false,
])

{{-- count and dot make the round count badge of shadcn's examples; a zero count is hidden unless show-zero. --}}
@php
$attributes = $attributes->merge(['data-slot' => 'badge', 'data-variant' => $variant, 'data-count' => $count !== null, 'data-dot' => $dot]);
@endphp

@if ($count === null || $count > 0 || $showZero)
@if ($attributes->has('href'))
<a {{ $attributes }}>@if ($count !== null){{ $count > $max ? $max.'+' : $count }}@else{{ $slot }}@endif</a>
@else
<span {{ $attributes }}>@if ($count !== null){{ $count > $max ? $max.'+' : $count }}@else{{ $slot }}@endif</span>
@endif
@endif
