@blaze

@props([
    'isActive' => false,
    'size' => 'icon',
])

{{-- A Button: an <a> with href, otherwise a <button> for wire:click. --}}
<x-ui::button :variant="$isActive ? 'outline' : 'ghost'" :size="$size" :attributes="$attributes->merge(['aria-current' => $isActive ? 'page' : false, 'data-active' => $isActive])">{{ $slot }}</x-ui::button>
