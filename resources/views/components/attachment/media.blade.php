@blaze
@props(['variant' => 'icon'])
<div {{ $attributes->merge(['data-slot' => 'attachment-media', 'data-variant' => $variant]) }}>{{ $slot }}</div>
