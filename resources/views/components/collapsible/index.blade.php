@props(['open' => false])
<div {{ $attributes->class('ui-collapsible') }} data-ui-disclosure x-data="uiDisclosure(@js($open))" x-modelable="opened" x-id="['ui-collapsible']" x-bind:data-panel-open="opened ? '' : null">{{ $slot }}</div>
