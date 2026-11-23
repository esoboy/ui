@props(['initialOpen' => false])
<div {{ $attributes->class('ui-trace-collapsible') }} x-data="uiDisclosure(@js($initialOpen))" x-modelable="opened" x-id="['ui-trace']">{{ $slot }}</div>
