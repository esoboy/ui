@blaze
@props(['color' => null, 'variant' => 'default'])
<div {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'alert', $attributes)->class('group/alert')->merge(['role' => 'alert', 'data-esoboy-ui' => '', 'data-slot' => 'alert', 'data-variant' => $variant]) }}>
    {{ $slot }}</div>
