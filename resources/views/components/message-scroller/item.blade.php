@blaze

@props([
    'messageId' => null,
    'scrollAnchor' => false,
])

{{-- Rows need a stable wire:key (and messageId to be jumped to) so morphs keep their place. --}}
<div {{ $attributes->merge(['data-slot' => 'message-scroller-item', 'data-message-id' => $messageId ?? false, 'data-scroll-anchor' => $scrollAnchor ? 'true' : 'false']) }}>{{ $slot }}</div>
