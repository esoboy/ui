@blaze
@props(['color' => null, 'variant' => 'default', 'align' => 'start'])
<div {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'bubble', $attributes)->class('group/bubble')->merge(['data-esoboy-ui' => '', 'data-slot' => 'bubble', 'data-variant' => $variant, 'data-align' => $align]) }}>{{ $slot }}</div>
