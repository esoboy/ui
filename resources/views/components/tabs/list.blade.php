@props(['size' => 'default', 'variant' => 'default'])
@aware(['orientation' => 'horizontal'])
<div role="tablist" aria-orientation="{{ $orientation }}" {{ $attributes->class(['group/tabs-list ui-tabs-list', 'ui-tabs-list-variant-'.$variant])->merge(['data-size' => $size]) }} x-on:keydown="navigate($event)">
    {{ $slot }}</div>
