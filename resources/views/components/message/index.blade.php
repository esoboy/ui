@blaze
@props(['align' => 'start'])
<article {{ $attributes->class('ui-message group/message')->merge(['data-align' => $align]) }}>{{ $slot }}</article>
