@props(['value'])
<div {{ $attributes->merge(['data-slot' => 'navigation-menu-content', 'data-value' => $value]) }} hidden>{{ $slot }}</div>
