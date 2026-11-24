@props(['initialOpen' => false])
<div {{ $attributes->class('ui-trace-collapsible') }} data-ui-disclosure x-data="uiTraceDisclosure(@js($initialOpen))" x-modelable="opened" x-id="['ui-trace']">{{ $slot }}</div>
