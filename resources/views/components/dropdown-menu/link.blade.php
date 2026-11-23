@props(['variant' => 'default', 'inset' => false])
<a role="menuitem" {{ $attributes->merge(['class' => 'ui-dropdown-menu-link ui-menu-item ui-menu-item-variant-'.$variant, 'data-inset' => $inset ? '' : null]) }}
    x-on:click="select()">{{ $slot }}</a>
