@props(['inset' => false])
<x-ui::dropdown-menu.label :inset="$inset" {{ $attributes->class('ui-menubar-label') }}>{{ $slot }}</x-ui::dropdown-menu.label>
