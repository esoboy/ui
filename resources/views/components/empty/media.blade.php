@blaze
@props(['variant' => 'default'])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'empty-media', 'data-variant' => $variant]) }}>{{ $slot }}</div>
