@blaze
@props(['link' => null])
@if ($link !== null)
<a {{ $attributes->except('href')->class('ui-text-link')->merge(['href' => $link, 'data-esoboy-ui' => '', 'data-slot' => 'text']) }}>{{ $slot }}</a>
@else
<p {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'text']) }}>{{ $slot }}</p>
@endif
