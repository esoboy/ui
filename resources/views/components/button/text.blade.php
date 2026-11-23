@blaze
@props(['color' => null, 'variant' => 'default', 'size' => 'default'])
<span {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'button', $attributes)->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant])->merge(['data-esoboy-ui' => '', 'data-slot' => 'button']) }}>{{ $slot }}</span>
