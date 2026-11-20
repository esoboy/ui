@props(['value'])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'navigation-menu-content', 'data-value' => $value]) }} hidden>{{ $slot }}</div>
