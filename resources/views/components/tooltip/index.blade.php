@props(['openDelay' => 600, 'closeDelay' => 0, 'disabled' => false])
<div {{ $attributes->merge(['class' => 'ui-tooltip', 'data-disabled' => $disabled ? 'true' : 'false']) }} data-ui-overlay x-data="uiPopover" x-id="['ui-popover']"
    x-on:pointerenter="if ($event.pointerType !== 'touch') hover(true, @js($openDelay))"
    x-on:pointerleave="hover(false, @js($closeDelay))" x-on:focusin="open(false)"
    x-on:focusout="if (!$root.contains($event.relatedTarget)) close()">{{ $slot }}</div>
