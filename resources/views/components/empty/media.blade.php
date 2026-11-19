@blaze
@props(['variant' => 'default'])
<div {{ $attributes->merge(['data-slot' => 'empty-media', 'data-variant' => $variant]) }}>{{ $slot }}</div>
