@props(['size' => 'default', 'variant' => 'default', 'activateOnFocus' => false])
@aware(['tabsOrientation' => 'horizontal'])
<div role="tablist" aria-orientation="{{ $tabsOrientation }}" {{ $attributes->class(['group/tabs-list ui-tabs-list', 'ui-tabs-list-variant-'.$variant])->merge(['data-size' => $size]) }} x-on:keydown="navigate($event, @js($activateOnFocus))">
    {{ $slot }}</div>
