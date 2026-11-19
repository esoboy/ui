<div {{ $attributes->except(['aria-label', 'tabindex'])->merge(['data-slot' => 'message-scroller']) }} x-data="uiMessageScroller">
    <div data-slot="message-scroller-viewport" x-on:scroll.passive="measure()" {{ $attributes->only(['aria-label', 'tabindex'])->merge(['tabindex' => 0]) }}>
        <div data-slot="message-scroller-content" data-ui-messages>{{ $slot }}</div>
    </div>
    @isset($controls){{ $controls }}@endisset
</div>
