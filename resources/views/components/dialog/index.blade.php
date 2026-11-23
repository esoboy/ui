@props(['initialOpen' => false])
<div {{ $attributes->class('ui-dialog') }} data-ui-overlay x-data="uiDialog(@js($initialOpen))" x-modelable="opened" x-id="['ui-dialog']">
    {{ $slot }}</div>
