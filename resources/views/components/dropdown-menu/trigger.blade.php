@blaze(fold: true)

@props([
    'as' => 'button',
])

@php
// commandfor stays empty: ui.js points the trigger at the Dropdown Menu Content after it.
$attributes = $attributes->merge(['data-dropdown-menu-trigger' => true, 'command' => 'toggle-popover', 'commandfor' => '', 'aria-haspopup' => 'menu']);
@endphp

{{-- as renders another component as the trigger, like shadcn's render (sidebar.menu-button, tooltip.trigger, …); it must render a <button>, whose command the browser runs. --}}
@if ($as === 'button')
<x-ui::button :attributes="$attributes">{{ $slot }}</x-ui::button>
@else
<x-dynamic-component :component="'ui::'.$as" :attributes="$attributes">{{ $slot }}</x-dynamic-component>
@endif
