@props(['variant' => 'default', 'inset' => false])
<button type="button" role="menuitem" {{ $attributes->merge(['class' => 'ui-menu-item ui-menu-item-variant-'.$variant, 'data-inset' => $inset ? '' : null]) }}
    x-on:click="select()">{{ $slot }}</button>
