@blaze
@props(['color' => null])
<span {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'avatar', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'avatar-fallback']) }} x-show="!loaded">{{ $slot }}</span>
