@props(['variant' => 'default'])
@aware(['orientation' => 'horizontal'])
<div role="tablist" aria-orientation="{{ $orientation }}" {{ $attributes->class(['group/tabs-list ui-tabs-list', 'ui-tabs-list-variant-'.$variant]) }} x-on:keydown="navigate($event)">
    {{ $slot }}</div>
