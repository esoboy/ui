<div role="tablist" aria-orientation="{{ $tabsScope->tabsOrientation }}" {{ $attributes->class(['group/tabs-list ui-tabs-list', 'ui-tabs-list-variant-'.$variant])->merge(['data-size' => $size]) }} x-on:keydown="navigate($event, @js($activateOnFocus))">
    @includeIsolated('ui::partials.tabs-parts', ['parts' => $children, 'scope' => $tabsScope])</div>
