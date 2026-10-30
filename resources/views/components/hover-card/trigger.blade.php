@blaze(fold: true)

@props([
    'as' => 'button',
    'delay' => 600,
    'closeDelay' => 300,
])

@php
// Hovering or focusing it opens the Hover Card Content after it, after the delay (ui.js).
$attributes = $attributes->merge(['data-hover-card-trigger' => true, 'data-delay' => $delay, 'data-close-delay' => $closeDelay]);
@endphp

{{-- A Button (a link with href) unless as names another component, like shadcn's render. --}}
@if ($as === 'button')
<x-ui::button :attributes="$attributes">{{ $slot }}</x-ui::button>
@else
<x-dynamic-component :component="'ui::'.$as" :attributes="$attributes">{{ $slot }}</x-dynamic-component>
@endif
