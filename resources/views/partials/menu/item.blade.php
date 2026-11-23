@props(['color' => null, 'variant' => 'default', 'inset' => false])
<button type="button" role="menuitem" {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'menu', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'menu-item', 'data-variant' => $variant, 'data-inset' => $inset ? '' : null]) }}
    x-on:click="select()">{{ $slot }}</button>
