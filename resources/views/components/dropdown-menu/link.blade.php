@props(['color' => null, 'variant' => 'default', 'inset' => false])
<a role="menuitem" {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'menu', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'menu-item', 'data-variant' => $variant, 'data-inset' => $inset ? '' : null]) }}
    x-on:click="select()">{{ $slot }}</a>
