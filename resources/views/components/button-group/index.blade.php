@blaze
@props(['orientation' => 'horizontal'])
<div {{ $attributes->merge(['class' => 'ui-button-group', 'data-orientation' => $orientation]) }} role="group">{{ $slot }}</div>
