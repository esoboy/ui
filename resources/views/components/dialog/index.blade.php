@props(['initialOpen' => false])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'dialog']) }} data-ui-overlay x-data="uiDialog(@js($initialOpen))" x-modelable="opened" x-id="['ui-dialog']">
    {{ $slot }}</div>
