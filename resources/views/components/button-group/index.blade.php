@blaze
@props(['orientation' => 'horizontal'])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'button-group', 'data-orientation' => $orientation]) }} role="group">{{ $slot }}</div>
