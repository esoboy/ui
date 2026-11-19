@blaze
@props(['orientation' => 'horizontal'])
<div {{ $attributes->merge(['data-slot' => 'button-group', 'data-orientation' => $orientation]) }} role="group">{{ $slot }}</div>
