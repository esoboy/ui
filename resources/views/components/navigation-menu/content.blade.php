@props(['value'])
<div {{ $attributes->merge(['class' => 'ui-navigation-menu-content', 'data-value' => $value]) }} hidden>{{ $slot }}</div>
