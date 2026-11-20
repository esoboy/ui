@props(['variant' => 'default', 'inset' => false])
<button type="button" role="menuitem" {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'menu-item', 'data-variant' => $variant, 'data-inset' => $inset ? '' : null]) }}
    x-on:click="select()">{{ $slot }}</button>
