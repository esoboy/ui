@props(['initialOpen' => false])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'trace-collapsible']) }} x-data="uiDisclosure(@js($initialOpen))" x-modelable="opened" x-id="['ui-trace']">{{ $slot }}</div>
