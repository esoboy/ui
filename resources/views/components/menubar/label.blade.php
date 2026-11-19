@props(['inset' => false])
<x-ui::dropdown-menu.label :inset="$inset" {{ $attributes->merge(['data-slot' => 'menubar-label']) }}>{{ $slot }}</x-ui::dropdown-menu.label>
