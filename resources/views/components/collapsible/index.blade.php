@props(['open' => false])
<div {{ $attributes->merge(['data-slot' => 'collapsible']) }} x-data="uiDisclosure(@js($open))" x-modelable="opened" x-id="['ui-collapsible']" x-bind:data-panel-open="opened ? '' : null">{{ $slot }}</div>
