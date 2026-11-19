@props(['variant' => 'default'])
@aware(['orientation' => 'horizontal'])
<div role="tablist" aria-orientation="{{ $orientation }}" {{ $attributes->class('group/tabs-list')->merge(['data-slot' => 'tabs-list', 'data-variant' => $variant]) }} x-on:keydown="navigate($event)">
    {{ $slot }}</div>
