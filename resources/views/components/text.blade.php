@blaze
@props(['link' => null])
@if ($link !== null)
<a {{ $attributes->except('href')->class('ui-text-link')->merge(['href' => $link, 'class' => 'ui-text']) }}>{{ $slot }}</a>
@else
<p {{ $attributes->class('ui-text') }}>{{ $slot }}</p>
@endif
