@aware(['disclosure' => null])
<div {{ $attributes->except('class')->class('ui-trace-collapsible-content') }} data-ui-panel data-ui-disclosure-panel
    @unless ($attributes->has('id')) x-bind:id="$id('ui-trace')" @endunless
    x-bind:data-open="opened" wire:bind:hidden="!opened" @if (! $disclosure?->isOpen()) hidden @endif>
    <div class="ui-trace-collapsible-clip">
        <div {{ $attributes->only('class')->class('ui-trace-collapsible-content-inner') }}>{{ $slot }}</div>
    </div>
</div>
