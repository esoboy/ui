@blaze
@props(['status' => null, 'size' => 'default', 'link' => null])
@if ($link !== null)
<a {{ $attributes->except('href')->class(['ui-text ui-text-link', \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['href' => $link, 'data-size' => $size]) }}>{{ $slot }}</a>
@else
<p {{ $attributes->class(['ui-text', \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['data-size' => $size]) }}>{{ $slot }}</p>
@endif
