<div {{ $attributes->except('class')->merge(['data-esoboy-ui' => '', 'data-slot' => 'trace-collapsible-content']) }} data-ui-panel
    @unless ($attributes->has('id')) x-bind:id="$id('ui-trace')" @endunless
    x-bind:data-open="opened" x-show="opened" x-cloak>
    <div data-esoboy-ui data-slot="trace-collapsible-content-inner" {{ $attributes->only('class') }}>{{ $slot }}</div>
</div>
