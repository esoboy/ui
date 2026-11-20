@props(['openDelay' => 600, 'closeDelay' => 300])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'hover-card']) }} data-ui-overlay x-data="uiPopover" x-id="['ui-popover']"
    x-on:pointerenter="if ($event.pointerType !== 'touch') hover(true, @js($openDelay))"
    x-on:pointerleave="hover(false, @js($closeDelay))" x-on:focusin="if ($event.target.matches(':focus-visible')) hover(true, @js($openDelay))"
    x-on:focusout="if (!$root.contains($event.relatedTarget)) close()">{{ $slot }}</div>
