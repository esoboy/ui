<div {{ $attributes }} data-ui-overlay @if($tooltip) data-ui-trigger-proxy @endif x-data="uiPopover" x-id="['ui-popover']"
    x-on:pointerenter="if ($event.pointerType !== 'touch') hover(true, @js($openDelay))"
    x-on:pointerleave="hover(false, @js($closeDelay))"
    @if($tooltip) x-on:focusin="open(false)" @else x-on:focusin="if ($event.target.matches(':focus-visible')) hover(true, @js($openDelay))" @endif
    x-on:focusout="if (!$root.contains($event.relatedTarget)) close()">{{ $slot }}</div>
