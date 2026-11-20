@props(['inset' => false])
<x-ui::dropdown-menu.label :inset="$inset" {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'menubar-label']) }}>{{ $slot }}</x-ui::dropdown-menu.label>
