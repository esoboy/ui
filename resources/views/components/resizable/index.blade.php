@props(['direction' => 'horizontal'])
<div {{ $attributes->merge(['class' => 'ui-resizable', 'data-direction' => $direction]) }} x-data="uiResizable(@js($direction))"
    x-on:pointermove.window="move($event)" x-on:pointerup.window="finish()" x-on:pointercancel.window="finish()">
    {{ $slot }}</div>
