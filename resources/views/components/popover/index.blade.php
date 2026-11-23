<div {{ $attributes->class('ui-popover') }} data-ui-overlay x-data="uiPopover" x-id="['ui-popover']"
    x-on:focusout="if (opened && $event.relatedTarget && !$el.contains($event.relatedTarget)) close()">{{ $slot }}</div>
