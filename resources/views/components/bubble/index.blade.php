@blaze
@props(['variant' => 'default', 'align' => 'start'])
<div {{ $attributes->class('group/bubble')->merge(['class' => 'ui-bubble ui-bubble-variant-'.$variant, 'data-align' => $align]) }}>{{ $slot }}</div>
