@aware(['disclosure' => null])
<div {{ $attributes->class('ui-collapsible-content') }} data-ui-panel data-ui-disclosure-panel @unless($attributes->has('id')) x-bind:id="$id('ui-collapsible')" @endunless x-bind:data-panel-open="opened ? '' : null" wire:bind:hidden="!opened" @if (! $disclosure?->isOpen()) hidden @endif>{{ $slot }}</div>
