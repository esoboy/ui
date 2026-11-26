@props(['initialOpen' => false])
<div {{ $attributes->whereDoesntStartWith('wire:model')->except('value')->class('ui-alert-dialog') }} @if ($attributes->whereStartsWith('x-model')->isNotEmpty()) x-modelable="opened" @endif data-ui-overlay x-data="uiDialog(@js($initialOpen))"
    x-id="['ui-dialog']">@includeIsolated('ui::partials.dialog-state', ['kind' => 'alert-dialog', 'attributes' => $attributes, 'initialOpen' => $initialOpen])
    {{ $slot }}</div>
