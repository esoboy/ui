<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'popover']) }} data-ui-overlay x-data="uiPopover" x-id="['ui-popover']"
    x-on:focusout="if (opened && $event.relatedTarget && !$el.contains($event.relatedTarget)) close()">{{ $slot }}</div>
