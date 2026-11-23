@blaze
@props(['variant' => 'default'])
<div {{ $attributes->class('ui-item-media ui-item-media-variant-'.$variant) }}>{{ $slot }}</div>
