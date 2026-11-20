@blaze
@props(['variant' => 'icon'])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'attachment-media', 'data-variant' => $variant]) }}>{{ $slot }}</div>
