<div {{ $attributes->except(['aria-label', 'tabindex'])->class('ui-message-scroller') }} x-data="uiMessageScroller">
    <div x-on:scroll.passive="measure()" {{ $attributes->only(['aria-label', 'tabindex'])->merge(['tabindex' => 0])->class('ui-message-scroller-viewport') }}>
        <div class="ui-message-scroller-content" data-ui-messages>{{ $slot }}</div>
    </div>
    @isset($controls){{ $controls }}@endisset
</div>
