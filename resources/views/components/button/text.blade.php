@blaze
@props(['variant' => 'default', 'size' => 'default'])
<span {{ $attributes->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant])->merge(['data-esoboy-ui' => '', 'data-slot' => 'button']) }}>{{ $slot }}</span>
