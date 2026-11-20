@blaze
@props(['align' => 'start'])
<article {{ $attributes->class('group/message')->merge(['data-esoboy-ui' => '', 'data-slot' => 'message', 'data-align' => $align]) }}>{{ $slot }}</article>
