@props(['variant' => 'default', 'inset' => false])
<a role="menuitem" {{ $attributes->merge(['data-slot' => 'menu-item', 'data-variant' => $variant, 'data-inset' => $inset ? '' : null]) }}
    x-on:click="select()">{{ $slot }}</a>
