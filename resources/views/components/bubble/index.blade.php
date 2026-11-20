@blaze
@props(['variant' => 'default', 'align' => 'start'])
<div {{ $attributes->class('group/bubble')->merge(['data-esoboy-ui' => '', 'data-slot' => 'bubble', 'data-variant' => $variant, 'data-align' => $align]) }}>{{ $slot }}</div>
