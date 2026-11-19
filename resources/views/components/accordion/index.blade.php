@props(['multiple' => false, 'collapsible' => true])
<div {{ $attributes->merge(['data-slot' => 'accordion']) }} x-data="uiAccordion" x-on:keydown="navigate($event)" x-id="['ui-accordion']">{{ $slot }}</div>
