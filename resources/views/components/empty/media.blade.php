@blaze
@props(['variant' => 'default'])
<div {{ $attributes->class('ui-empty-media ui-empty-media-variant-'.$variant) }}>{{ $slot }}</div>
