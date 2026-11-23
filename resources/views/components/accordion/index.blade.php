@props(['multiple' => false, 'collapsible' => true])
<div {{ $attributes->class('ui-accordion') }} x-data="uiAccordion" x-on:keydown="navigate($event)" x-id="['ui-accordion']">{{ $slot }}</div>
