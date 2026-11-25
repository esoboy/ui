@props(['direction' => 'horizontal'])
<div {{ $attributes->merge(['class' => 'ui-resizable', 'data-direction' => $direction]) }} x-data="uiResizable(@js($direction))">
    {{ $slot }}</div>
