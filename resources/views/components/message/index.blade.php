@blaze
@props(['align' => 'start'])
<article {{ $attributes->class('group/message')->merge(['data-slot' => 'message', 'data-align' => $align]) }}>{{ $slot }}</article>
