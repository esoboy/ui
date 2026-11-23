@blaze
@props(['variant' => 'icon'])
<div {{ $attributes->class('ui-attachment-media ui-attachment-media-variant-'.$variant) }}>{{ $slot }}</div>
