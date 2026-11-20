@blaze
@props(['variant' => 'default'])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'item-media', 'data-variant' => $variant]) }}>{{ $slot }}</div>
